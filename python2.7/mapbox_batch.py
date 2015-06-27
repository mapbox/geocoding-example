# EXAMPLE BATCH GEOCODING SCRIPT
# ------------------------------
# Usage: python mapbox_batch.py input.txt /path/to/output_dir/
# Note: script requires that the environment variable MAPBOX_ACCESS_TOKEN be set

import __future__
import json, sys, urllib, os.path, time
import requests, gevent, gevent.pool

class MapboxBatchGeocoder(object):
    """
    Sample implementation of batch geocoding with rate limiting & concurrency.

    Args:
        access_token (str): valid Mapbox access token with permanent geocoding permissions
        batch_size (Optional[int]): number of features to geocode per query
        parallelism (Optional[int]): number of simultaneous http connections to use. None = no limit.
    """
    def __init__(self, access_token, batch_size=50, parallelism=5):
        self.access_token = access_token
        self.batch_size = batch_size
        self.ratelimit_delay = None # initial value; ignored after first response
        if parallelism is None: # None = use as many http connections as ratelimit allows
            self.spawner = gevent
        else:
            self.spawner = gevent.pool.Pool(parallelism)

    def _chunks(self, f, chunk_size):
        batch = []
        for line in f:
            batch.append(line)
            if len(batch) >= chunk_size:
                yield batch
                batch = []
        if len(batch):
            yield batch

    def _send(self, chunk, path):
        response = requests.get('http://api.tiles.mapbox.com/v4/geocode/mapbox.places-permanent/{}.json?access_token={}'.format(';'.join([urllib.quote_plus(s.strip()) for s in chunk]), self.access_token))
        if response.status_code == 200:
            print('- response received, saving to {}'.format(path))
            with open(path, 'w') as output_file:
                json.dump(response.json(), output_file, indent=4)
            self.ratelimit_delay = float(response.headers.get('x-rate-limit-interval')) / float(response.headers.get('x-rate-limit-limit')) + 0.1
        else:
            print('# {} error: {}'.format(response.status_code, response.text))

    def geocode(self, src, dst):
        """
        Convert queries to output file(s).

        Args:
            src (file-like): a file-like object of queries (one per line)
            dst (str): the system path into which results will be placed (as .json files)
        """
        for (i, chunk) in enumerate(self._chunks(src, self.batch_size)):
            output_filename = '{}/{}.json'.format(dst, i)
            if i == 0:
                self._send(chunk, output_filename) # block on first call to get ratelimit
            else:
                self.spawner.spawn(self._send, chunk, output_filename)
            gevent.sleep(self.ratelimit_delay)

if __name__ == '__main__':
    MAPBOX_ACCESS_TOKEN = os.environ.get('MAPBOX_ACCESS_TOKEN', False)
    if not MAPBOX_ACCESS_TOKEN:
        print('environment variable MAPBOX_ACCESS_TOKEN must be set')
        sys.exit(1)

    (input_path, output_path) = map(os.path.abspath, sys.argv[1:3])
    for path in (input_path, output_path):
        if not os.path.exists(path):
            print('{} does not exist'.format(path))
            sys.exit(1)

    mapbox = MapboxBatchGeocoder(MAPBOX_ACCESS_TOKEN)
    with open(input_path, 'r') as input_file:
        mapbox.geocode(input_file, output_path)
