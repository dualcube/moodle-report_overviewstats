### v1.6.1 ###

* Fixed - Report disabled for site as a course (i.e. when course is 1)
* Fixed - Removed deprecated lib files
* Added - support to for moodle 4.0+
  
### v1.6.0 ###

* Added Support for moodle 4.3.
* Added Support for render chart with Core charts API.
* Fixed Remove support of YUI for render chart.


### v1.5.1 ###

* Added `composer.json` to facilitate loading Moodle Plugins as dependencies. Credit goes to @michaelmeneses for the addition.
* Code cleanup to pass more prechecks.
* Confirmed to work in Moodle 3.10.

### v1.5 ###

* Fixed issue #12 - Missing privacy provider. Credit goes
  to @golenkovm for the fix.
* Confirmed to work in Moodle 3.9 and 3.10.

### v1.4 ###

* Fixed issue #10 - deprecated class `coursecat` removed from Moodle 3.10. Credit goes
  to Eric Bram @sei-ebram for the fix.
* Confirmed by users to work in Moodle 3.9 and 3.10.


### v1.3 ###

* Fixed critical error thrown in Moodle 2.9 by switching to `\core\log\sql_reader`. Credit goes to Andrew Davis (@andyjdavis) for
  spotting and debugging this.
