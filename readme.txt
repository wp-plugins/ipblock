=== Plugin Name ===
Contributors: helium-3
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=P5UC9VG3Q687N
Tags: plugin, admin, best security plugins, login, bruteforce, login throttling, security, protection, ip, block, ipblock,  ban, auth, authentication, botnet, brute force, harden wp, limit login attempts, limit logins, lockdown
Requires at least: 3.5
Tested up to: 4.1
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

IPBlock limits login attempts. There are 2 modes: set a small delay after each login attempt or allow number of attempts in given time.

== Description ==

How it works:

Mode 1  set a delay after each attempt e.g.
<ul>
		<li>1 attempt = no delay</li>
		<li>2 attempts = 5 sec delay</li>
		<li>3-4 attempts = 15 sec delay</li>
		<li>5-9 attempts = 30 sec delay</li>
		<li>10+ attempts = 45 sec delay</li></ul>
This is just an example! You can program it however you want.

Mode 2 -  allow a number of attempts in given time.
		For example allow 5 attempts in 15 minutes.

Time left is formatted properly e.g. 10 seconds, 1 minute, 3 hours

This plugin doesn't use plugabble functions and should be compatible with any other plugin.


== Installation ==

1. Upload `ipblock` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to settings in your dashboard and choose IPBlock to change settings

== Frequently Asked Questions ==

= What is delay scheme? How to use it? =

<p>Scheme dictates what delay to set after a number of failed login attempts. It's used if you choose Mode 1. It's composed of pairs login_attempts(+)=>delay (in seconds); Lets start with a single rule, e.g. 5=>10; This rule tells to set a delay of 10 seconds after 5 or more login attempts. Lets add aother rule:</p>

5=>10; 10=>20;

<p>10 seconds will be set on 5 or more attempts, but there's a rule for 10 or more attempts, so this second rule is more important in its scope. The result of this will be</p>

*5-9 attempts = 10 second delay
*10 or more attempts - 20 seconds delay

Another examples:

1=>2; 2=>4; 3=>8; 4=>16; 5=>32; 8=>64;
<ul>
<li>1 attempt = 2seconds</li>
<li>2 attempts = 4 seconds</li>
<li>3 attempts = 8 seconds</li>
<li>4 attempts = 16 seconds</li>
<li>5-7 attempts = 32 seconds</li>
<li>8 or more attempts = 64 seconds</li>
</ul>
2=>5; 3=>15; 5=>30; 10=>45;
<ul>
<li>1 attempt = no delay</li>
<li>2 attempts = 5 sec delay</li>
<li>3-4 attempts = 15 sec delay</li>
<li>5-9 attempts = 30 sec delay</li>
<li>10+ attempts = 45 sec delay</li>
</ul>

= What is record expiration time? =

<p>This option is also only for Mode 1. Record expiration time tells how long to track an ip after last login attempt. An ip record has a certain expiration timestamp, when it expires it is treated as it doesn't exist and is pending removal. Every time a login attempt is made expriation timestamp is set to a sum of current timestamp and record expiration time. For example if an ip has 20 login attempts and record expiration time is 60 seconds and if user of that ip won't log in in next 60 seconds, the record will be no longer valid and ip will be treated as if it made 0 attempts.</p>

== Screenshots ==

1. delay was set notice (Mode 1)
2. cannot login yet error
3. number of attempts used notice (Mode 2)
4. all attempts used notice (Mode 2)

Note that 'login protection by IPBlock' text is optional

== Changelog ==

1.0 - Initial release (December 18 2014)
