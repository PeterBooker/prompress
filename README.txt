=== PromPress ===
Contributors: PeterBooker
Tags: metrics, monitoring, performance
Requires at least: 6.4
Tested up to: 6.7.2
Stable tag: 1.2.3
Requires PHP: 8.1
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Monitor the performance and health of your site with Prometheus.

== Description ==

PromPress tracks various website and WordPress related metrics for collection by [Prometheus](https://prometheus.io/), which allows you to monitor your site's performance and health. You can even setup dashboards with [Grafana](https://grafana.com/) and/or setup alerting via [Prometheus Alertmanager](https://prometheus.io/docs/alerting/latest/alertmanager/).

(Note: Requires Object Caching via Redis to be active, so that the plugin can store metrics.)

We track a range of Website and WordPress specific metrics:

* Request Count
* Request Duration
* Request Peak Memory Usage
* External Request Duration
* Query Count
* Query Duration
* Plugin Updates
* Theme Updates
* Emails Sent
* User Count
* Post Count
* Option Count

This gives you the ability to monitor the performance of your website over time and get an early warning of potential problems, like your site sending a lot of emails or the post count increasing a lot.

We purposefully avoid general software and/or server level metrics which are better handled outside of the website, like detailed database metrics which is better handled via the [mysqld exporter](https://github.com/prometheus/mysqld_exporter).

== Frequently Asked Questions ==

= Does this require external service(s)? =

Yes. This plugin requires you to have [Prometheus](https://prometheus.io/) setup and collecting the metrics from your site. You can also use [Alertmanager](https://prometheus.io/docs/alerting/latest/alertmanager/) and [Grafana](https://grafana.com/) to get more out of it.

= How do I configure Prometheus to scrape metrics from my site? =

You can use this snippet in your Prometheus config (just update the domain under targets):

```yml
  - job_name: 'wordpress'
    scrape_interval: 15s
    metrics_path: /wp-json/prompress/v1/metrics
    static_configs:
      - targets:
        - 'example.com'
```

== Changelog ==

= 1.2.1 =

* Bugfix- Properly sets the Redis prefix.
* Bugfix- Adds a filter for the Redis prefix.

= 1.2.0 =

* Bugfix- Multisite support. Uses a unique Redis key per site, allowing multiple sites in a network and/or on the same server to share a Redis server.

= 1.0.0 =

* Initial release.
