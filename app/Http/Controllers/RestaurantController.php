<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RestaurantController extends Controller
{
    public function search(Request $request)
    {
        $city = $request->input('city');

        // Step 1: Get city coordinates via Nominatim
        $geoResponse = Http::withHeaders([
            'User-Agent' => 'Laravel-RestaurantFinder/1.0 (dotasarasampati6@gmail.com)',
        ])->get("https://nominatim.openstreetmap.org/search", [
            'q' => $city,
            'format' => 'json',
            'limit' => 1,
        ]);

        if (!$geoResponse->ok() || empty($geoResponse->json())) {
            return back()->with('error', "City '{$city}' not found.");
        }

        $geo = $geoResponse->json()[0];
        $lat = $geo['lat'];
        $lon = $geo['lon'];

        // Step 2: Overpass query using bounding box (around city center, 5km radius)
        $radius = 5000; // meters
        $overpassQuery = "
            [out:json][timeout:25];
            (
              node[\"amenity\"~\"restaurant|cafe|fast_food\"](around:$radius,$lat,$lon);
              way[\"amenity\"~\"restaurant|cafe|fast_food\"](around:$radius,$lat,$lon);
              relation[\"amenity\"~\"restaurant|cafe|fast_food\"](around:$radius,$lat,$lon);
            );
            out center;
        ";

        $overpassResponse = Http::withHeaders([
            'User-Agent' => 'Laravel-RestaurantFinder/1.0 (dotasarasampati6@gmail.com)',
        ])->asForm()->post("https://overpass-api.de/api/interpreter", [
            'data' => $overpassQuery,
        ]);

        if (!$overpassResponse->ok()) {
            return back()->with('error', "Failed to fetch restaurants.");
        }

        $elements = $overpassResponse->json()['elements'] ?? [];

        $restaurants = collect($elements)->map(function ($el) {
            $address = $el['tags']['addr:full']
                ?? trim(
                    ($el['tags']['addr:housenumber'] ?? '') . ' ' .
                    ($el['tags']['addr:street'] ?? '') . ', ' .
                    ($el['tags']['addr:city'] ?? '') . ' ' .
                    ($el['tags']['addr:state'] ?? '') . ' ' .
                    ($el['tags']['addr:postcode'] ?? '')
                );

            if (empty($address) || $address === ',' || $address === ', ,') {
                $lat = $el['lat'] ?? $el['center']['lat'] ?? null;
                $lon = $el['lon'] ?? $el['center']['lon'] ?? null;

                if ($lat && $lon) {
                    $address = "Lat: {$lat}, Lon: {$lon} | <a href='https://www.google.com/maps?q={$lat},{$lon}' target='_blank'>View on Map</a>";
                } else {
                    $address = "N/A";
                }
            }

            return [
                'name' => $el['tags']['name'] ?? 'Unknown',
                'address' => $address,
                'type' => $el['tags']['amenity'] ?? 'N/A',
            ];
        })->filter(fn($r) => $r['name'] !== 'Unknown')->take(20);

        if ($restaurants->isEmpty()) {
            return back()->with('error', "No restaurants found near {$city}.");
        }

        return view('welcome', [
            'restaurants' => $restaurants,
            'city' => $city,
        ]);
    }
}

