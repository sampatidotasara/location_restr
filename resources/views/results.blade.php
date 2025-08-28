<!DOCTYPE html>
<html>
<head>
    <title>Restaurants</title>
</head>
<body>
    <h1>Top Restaurants in {{ $city ?? '' }}</h1>

    @if($error)
        <p style="color: red;">{{ $error }}</p>
    @endif

    @if(!empty($restaurants))
        <ul>
            @foreach($restaurants as $r)
                <li>
                    <strong>{{ $r['name'] }}</strong><br>
                    Address: {{ $r['address'] ?: 'N/A' }}<br>
                    Type: {{ $r['type'] }}
                </li>
            @endforeach
        </ul>
    @else
        <p>No restaurants found.</p>
    @endif

    <a href="{{ url('/') }}">Back</a>
</body>
</html>

