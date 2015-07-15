var fs = require('fs'),
    http = require('http'),
    path = require('path'),
    through = require('through'),
    queue = require('queue-async');

function MapboxBatchGeocoder(access_token, dest, batch_size, parallelism) {
    batch_size = (typeof batch_size !== 'undefined') ?  batch_size : 50;
    parallelism = (typeof parallelism !== 'undefined') ? parallelism: 5;

    var queries = [],
        query_buffer = '',
        batch_index = 0,
        q = queue(parallelism);

    function geocode(queries, callback) {
        q.defer(function(cb) {
            http.get({
                host: 'api.tiles.mapbox.com',
                path: '/v4/geocode/mapbox.places-permanent/' + queries.map(encodeURIComponent).join(';') + '.json?access_token=' + access_token
            }, function(response) {
                var body = '';
                response.on('data', function(d) {
                    body += d;
                });
                response.on('error', function(e) {
                    callback(e);
                });
                response.on('end', function() {
                    callback(null, body);
                    setTimeout(cb, ((batch_size * parseFloat(response.headers['x-rate-limit-interval'])) / parseFloat(response.headers['x-rate-limit-limit'])) + 0.1 );
                });
            });
        });
    }

    function record_result(out_path) {
        return function(err, data) {
            if (err) return console.log('Error: ' + err);
            fs.writeFile(out_path, data, function() {
                console.log('Saved ' + out_path);
            });
        };
    }

    function thru_out(data) {
        query_buffer += data;
        var potential_queries = query_buffer.split('\n');
        potential_queries.forEach(function(part, part_i) {
            if (part_i === (potential_queries.length-1))
                query_buffer = part;
            else
                queries.push(part);

            if (queries.length >= batch_size) {
                geocode(queries, record_result(path.normalize(dest + '/' + batch_index + '.json')));
                batch_index += 1;
                queries = [];
            }
        });
    }

    function thru_end() {
        if (queries.length > 0) geocode(queries, record_result(path.normalize(dest + '/' + batch_index + '.json')));
    }

    return through(thru_out, thru_end);
}

if (require.main === module) {
    if (!process.env.MAPBOX_ACCESS_TOKEN) {
        console.log('environment variable MAPBOX_ACCESS_TOKEN must be set');
        process.exit(1);
    }

    var mapbox = MapboxBatchGeocoder(process.env.MAPBOX_ACCESS_TOKEN, path.resolve(process.argv[3]), 50, 5);
    fs.createReadStream(process.argv[2]).pipe(mapbox);
}

module.exports = MapboxBatchGeocoder;