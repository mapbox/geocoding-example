var fs = require('fs'),
    http = require('http'),
    path = require('path'),
    through = require('through'),
    queue = require('queue-async');

function MapboxBatchGeocoder(access_token, batch_size, parallelism) {
    batch_size = (typeof batch_size !== 'undefined') ?  batch_size : 50;
    parallelism = (typeof parallelism !== 'undefined') ? parallelism: 5;

    var queries = [],
        query_buffer = '',
        q = queue(parallelism);

    function geocode(queries, callback) {
        q.defer(function(cb) {
            var sent = +new Date();
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
                    setTimeout(cb, ((1000 * batch_size * parallelism * parseFloat(response.headers['x-rate-limit-interval'])) / parseFloat(response.headers['x-rate-limit-limit'])) - (+new Date() - sent) );
                });
            });
        });
    }

    function emit_result(err, data) {
        if (err) return console.log('Error: ' + err);
        this.emit('data', data);
    }

    function thru_out(data) {
        var that = this;
        query_buffer += data;
        var potential_queries = query_buffer.split('\n');
        potential_queries.forEach(function(part, part_i) {
            if (part_i === (potential_queries.length-1))
                query_buffer = part;
            else
                queries.push(part);

            if (queries.length >= batch_size) {
                geocode(queries, emit_result.bind(that));
                queries = [];
            }
        });
    }

    function thru_end() {
        if (queries.length > 0) geocode(queries, emit_result.bind(this));
    }

    return through(thru_out, thru_end);
}

if (require.main === module) {
    if (!process.env.MapboxAccessToken) {
        console.log('environment variable MapboxAccessToken must be set');
        process.exit(1);
    }

    var response_index = 0;
    var mapbox = MapboxBatchGeocoder(process.env.MapboxAccessToken, 50, 5);
    mapbox.on('data', function(data) {
        fs.writeFile(path.resolve(process.argv[3] + '/' + response_index + '.json'), data);
        console.log('storing ' + path.normalize(process.argv[3] + '/' + response_index) + '.json');
        response_index++;
    });
    fs.createReadStream(process.argv[2]).pipe(mapbox);
}

module.exports = MapboxBatchGeocoder;