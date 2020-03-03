ufw-vpn
=======

Introduction
------------

This script is designed to add the necessary rules for a VPN provider to a UFW firewall. It is
most useful if you have set your firewall to deny outgoing traffic by default except via the
VPN device, which would normally mean you'd have to temporarily disable your firewall in order
to make the initial VPN connection.

It is written in PHP so you will need to have that installed. In Debian or Ubuntu distros, it
is usually sufficient to install it thus:

    sudo apt-get install php7.0-cli

You may need to tweak that to `php7-cli` or `php7.1-cli`, etc. It should work fine with most
current versions of PHP7 and also PHP5. Feel free to report any bugs.

Usage
-----

Firewall rules are generated as a series of `ufw` commands. Simply specify your VPN's
address and the script will generate the necessary commands:

    ufw-vpn.php uk.myexamplevpn.net add > add-rules.sh

This will generate a list of rules of the form:

    ufw allow out to 1.2.3.4 port 443

You can then add the rules in the newly created script:

    chmod u+x add-rules.sh && sudo add-rules.sh

If you elect to create a delete script, the rules will look like so:

    ufw delete allow out to 1.2.3.4 port 443

You can also create a differences script, so that when your VPN provider updates their
set of tunnelling nodes, you can just update your rules, rather than zapping them all
and adding them all again. The command for that looks like

    ufw-vpn.php uk.myexamplevpn.net diff > diff-rules.sh
    chmod u+x diff-rules && sudo diff-rules

Set up GUFW
---

If you want the rules to be enforced, outgoing connections should be changed to Reject (they
are Allowed by default). This ensures that if the VPN connection drops, outbound traffic will
be stopped until it is reconnected.

For general security I also like to prevent incoming connections that are not explicitly allowed
by a rule. See the screenshot:

![GUFW config][1]

[1]: docs/gufw-config.png

Notes
-----

When deleting rules, the script resolves the IP addresses of your VPN using DNS afresh. This
means that you might get a different list if your provider has added new servers or removed
old ones. In that situation, the resulting script may fail to delete a rule that should be
deleted, or will try to delete one that does not exist. You can check the list after deletion
using `ufw` or the graphical interface `gufw`.

It is usually best to use diff instead of delete anyway, unless you wish to delete
these rules permanently.

A manual firewall entry is necessary to allow any traffic out on your VPN device. This can
be added using `gufw` when the VPN is enabled; note that you may have to restart the gufw
app, in order to refresh the list of interfaces.

This rule works fine for me, but your mileage may vary:

    From 10.4.0.0/16
    To anywhere
    Via interface tun0
    Going out

Testing
-------

Once you've installed the rules from this script, it can be tested in this fashion:

* Ensure that you can make a connection to your VPN whilst UFW is enabled. Do this
  several times so that a good number of connection servers are tested.
* Check that you can browse the web.
* Disconnect your VPN manually.
* Check that you can no longer browse the web.

Enhancements
------------

There are several possible enhancements, in particular the VPN port is hardwired, and I'd like
to change that. Feel free to send issues or PRs.

