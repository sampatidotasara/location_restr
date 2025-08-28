<!DOCTYPE html>
<html>
<head>
    <title>Top Restaurants</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        table, th, td { border: 1px solid #ccc; }
        th, td { padding: 10px; text-align: left; }
        th { background-color: #f4f4f4; }
    </style>
</head>
<body>
    <h1>Top Restaurants in {{ $city ?? 'your city' }}</h1>

    <form method="POST" action="{{ route('search') }}">
        @csrf
        <input type="text" name="city" placeholder="Enter city" required>
        <button type="submit">Search</button>
    </form>

    @if(session('error'))
        <p style="color:red;">{{ session('error') }}</p>
    @endif

    @if(isset($restaurants) && $restaurants->count())
        <table>
            <tr>
                <th>Name</th>
                <th>Address</th>
                <th>Type</th>
            </tr>
            @foreach($restaurants as $r)
                <tr>
                    <td>{{ $r['name'] }}</td>
                    <td>{!! $r['address'] !!}</td> {{-- Allow HTML (Google Maps link) --}}
                    <td>{{ $r['type'] }}</td>
                </tr>
            @endforeach
        </table>
    @endif
</body>
</html>

