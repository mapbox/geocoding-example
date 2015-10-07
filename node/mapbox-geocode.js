var https = require('https');

function geocode(mapboxAccessToken, query, callback) {
    https.get('https://api.tiles.mapbox.com/geocoding/v5/mapbox.places/' + encodeURIComponent(query) + '.json?access_token=' + mapboxAccessToken,
        function(response) {
            var body = '';
            response.on('data', function(d) {
                body += d;
            });
            response.on('error', function(e) {
                callback(e);
            });
            response.on('end', function() {
                callback(null, JSON.parse(body));
            });
        });
}

if (require.main === module) {
    if (!process.env.MapboxAccessToken) {
        console.log('environment variable MapboxAccessToken must be set');
        process.exit(1);
    }
    geocode(process.env.MapboxAccessToken, process.argv[2], function(err, result) {
        if (err) return console.log('Error: ' + err);
        console.log(JSON.stringify(result, null, 2));
    });
}

module.exports = geocode;