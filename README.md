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

    sudo apt-get install php5

It should work fine with most current versions of PHP5, and PHP7 should be fine as well. Feel
free to report any bugs.

Usage
-----

Firewall rules are generated as a series of `ufw` commands. Simply specify your VPN's
address and the script will generate the necessary commands:

    ufw-vpn.php uk.myexamplevpn.net add > add-rules.sh

This will generate a list of rules of the form:

    ufw allow out to 1.2.3.4 port 443

If you elect to create a delete script, the rules will look like so:

    ufw delete allow out to 1.2.3.4 port 443

Notes
-----

When deleting rules, the script resolves the IP addresses of your VPN using DNS afresh. This
means that you might get a different list if your provider has added new servers or removed
old ones. In that situation, the resulting script may fail to delete a rule that should be
deleted, or will try to delete one that does not exist. You can check the list after deletion
using `ufw` or the graphical interface `gufw`.

A manual firewall entry is necessary to allow any traffic out on your VPN device. This can
be added using `gufw` when the VPN is enabled; note that you may have to restart the gufw
app, in order to refresh the list of interfaces.

This rule works fine:

    From 10.4.0.0/16
    To anywhere
    Via interface tun0
    Going out

Enhancements
------------

There are several possible enhancements, in particular the VPN port is hardwired, and I'd like
to change that. Feel free to send issues or PRs.

