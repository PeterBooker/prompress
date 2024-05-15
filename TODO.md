# TODO

* Add `prompress_info` metric.
  * Gauge which always returns 1.
  * Labels: `php_version`, `mysql_version`, `wordpress_version`, `machine` (x86_64), more?
* Add `prompress_php_memory_usage` metric.
  * Histogram which tracks the current PHP memory usage.
