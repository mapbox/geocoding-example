var http = require('http');

function geocode(mapbox_access_token, query, callback) {
    http.get({
        host: 'api.tiles.mapbox.com',
        path: '/v4/geocode/mapbox.places-permanent/' + encodeURIComponent(query) + '.json?access_token=' + mapbox_access_token
    }, function(response) {
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