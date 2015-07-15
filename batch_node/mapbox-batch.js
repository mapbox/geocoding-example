var fs = require('fs'),
    https = require('https'),
    path = require('path'),
    through = require('through'),
    queue = require('queue-async');

function MapboxBatchGeocoder(mapboxAccessToken, batchSize, parallelism) {
    batchSize = (batchSize !== undefined) ?  batchSize : 50;
    parallelism = (parallelism !== undefined) ? parallelism: 5;

    var queries = [],
        queryBuffer = '',
        q = queue(parallelism);

    function geocode(queries, callback) {
        q.defer(function(cb) {
            var sent = +new Date();
            https.get('https://api.tiles.mapbox.com/v4/geocode/mapbox.places-permanent/' + queries.map(encodeURIComponent).join(';') + '.json?access_token=' + mapboxAccessToken,
                function(response) {
                    var body = '';
                    response.on('data', function(d) {
                        body += d;
                    });
                    response.on('error', function(e) {
                        callback(e);
                    });
                    response.on('end', function() {
                        callback(null, body);
                        setTimeout(cb, ((1000 * batchSize * parallelism * parseFloat(response.headers['x-rate-limit-interval'])) / parseFloat(response.headers['x-rate-limit-limit'])) - (+new Date() - sent) );
                    });
            });
        });
    }

    function emitResult(err, data) {
        if (err) return console.log('Error: ' + err);
        this.emit('data', data);
    }

    function thruOut(data) {
        var that = this;
        queryBuffer += data;
        var potentialQueries = queryBuffer.split('\n');
        potentialQueries.forEach(function(part, part_i) {
            if (part_i === (potentialQueries.length-1))
                queryBuffer = part;
            else
                queries.push(part);

            if (queries.length >= batchSize) {
                geocode(queries, emitResult.bind(that));
                queries = [];
            }
        });
    }

    function thruEnd() {
        if (queries.length > 0) geocode(queries, emitResult.bind(this));
    }

    return through(thruOut, thruEnd);
}

if (require.main === module) {
    if (!process.env.MapboxAccessToken) {
        console.log('environment variable MapboxAccessToken must be set');
        process.exit(1);
    }

    var responseIndex = 0;
    var mapbox = MapboxBatchGeocoder(process.env.MapboxAccessToken, 50, 5);
    mapbox.on('data', function(data) {
        fs.writeFile(path.resolve(process.argv[3] + '/' + responseIndex + '.json'), data);
        console.log('storing ' + path.normalize(process.argv[3] + '/' + responseIndex) + '.json');
        responseIndex++;
    });
    fs.createReadStream(process.argv[2]).pipe(mapbox);
}

module.exports = MapboxBatchGeocoder;