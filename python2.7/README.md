# Python 2.7

`batch.py` reads queries from an input text file, one per line, and constructs parallelized batch queries.

Results are stored in an output directory as JSON files.

## Requirements

- [grequests](https://crate.io/packages/grequests/)
- A Mapbox access token with batch geocoding capabilities (email sales@mapbox.com)

## Installation

`pip install -r requirements.txt`

## Usage

```
MAPBOX_ACCESS_TOKEN=__your access token__ python mapbox_batch.py input_file.txt /path/to/output/directory
```