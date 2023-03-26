# PromPress

PromPress is a WordPress plugin which allows you to monitor your install with Prometheus.

*Note: This is early development so the metrics and what they track may change significantly.*

## Dependencies

* [Prometheus](https://github.com/prometheus/prometheus)

## Requirements

* [PHP](https://github.com/php/php-src) 8.2
* [WordPress](https://github.com/WordPress/WordPress) 6.1
* [Redis](https://github.com/redis/redis)

## Prometheus Config

Here is an example Prometheus config for monitoring a site with this plugin active:

```yml
  - job_name: 'wordpress'
    scrape_interval: 15s
    metrics_path: /wp-json/prompress/v1/metrics
    static_configs:
      - targets:
        - 'domain.com'
```

As you can see, we use the WP REST API to expose the metrics endpoint, so you must set `metrics_path`.

## Plugin Setup

The plugin currently contains an options page under the `Settings` menu in the WordPress admin dashboard.

Currently the only option available is to toggle monitoring on/off.

## What do you monitor?

Currently, these are the metrics we create:

`remote_requests_duration_milliseconds` (histogram)
Tracks the duration of remote requests.

`queries_duration_seconds` (histogram)
Tracks the duration of database queries.

`posts_total` (gauge)
Tracks the total number of posts.

This list will be expanded over time.

## Development

There is a `docker-compose.yml` in the root (and config files in `/.docker`) which help setup a local development environment. However, it currently requires some manual steps- see the `/local` directory to get it running fully.

## Contributions

Feel free to create issues and pull requests.
