# VCL Configuration file for Saigon API Cache
#
# Default backend definition.  Set this to point to your content
# server.
# 

backend default {
  .host = "127.0.0.1";
  .port = "81";
}

acl purge {
    "127.0.0.1";
}

sub vcl_recv {

    set req.grace = 2m;

    if (req.restarts == 0) {
        if (req.http.x-forwarded-for) {
            set req.http.X-Forwarded-For =
		    req.http.X-Forwarded-For + ", " + client.ip;
        } else {
            set req.http.X-Forwarded-For = client.ip;
        }
    }

    # Purge Request Handling, check acls before purge
    if (req.request == "PURGE") {
        if (!client.ip ~ purge) {
            error 405 "Not allowed...";
        }
        return (lookup);
    }
    
    # Ban, "Purge", Requests based on Globs
    if (req.request == "BAN") {
        if (!client.ip ~ purge) {
            error 405 "Not allowed...";
        }
        ban_url(req.url);
        error 200 "Banned...";
    }

    # We only deal with GET by default from here on out
    if (req.request != "GET") {
        return (pass);
    }

    # Strip out cookies for static files, including possible version specifications
    if (req.url ~ "(?i)\.(png|gif|jpeg|jpg|ico|swf|css|js|html|htm|woff|ttf|eot|svg)(\?[a-zA-Z0-9\=\.\-]+)?$") {
        remove req.http.Cookie;
    }

    # Remove Google Analytics Cookie
    if (req.http.Cookie) {
        set req.http.Cookie = regsuball(req.http.Cookie, "(^|;\s*)(__[a-z]+|has_js)=[^;]*", "");
    }

    # Remove cookie if there is nothing specified
    if (req.http.Cookie == "") {
        remove req.http.Cookie;
    }

    # Real client detected, he'll get to pass through, lucky them
    if (req.http.Authorization || req.http.Cookie) {
        /* Not cacheable by default */
        return (pass);
    }

    # Possible UI API interaction, not caching results
    if (req.url !~ "(?i)(^\/sapi\/consumer|^\/api\/)") {
        return (pass);
    }
}

sub vcl_hit {
    if (req.request == "PURGE") {
        purge;
        error 200 "Purged...";
    }
}

sub vcl_miss {
    if (req.request == "PURGE") {
        purge;
        error 200 "Purged...";
    }
}

sub vcl_fetch {
    set beresp.grace = 2m;
    set beresp.http.x-url = req.url;
    set beresp.http.x-host = req.http.host;

    if (req.url ~ "\.(png|gif|jpg|swf|css|js|ico|html|htm|woff|eof|ttf|svg)$") {
        remove beresp.http.set-cookie;
    }

    if (beresp.http.Cache-Control ~ "(private|no-cache|no-store)") {
        set beresp.ttl = 0s;
    }
    else {
        set beresp.ttl = 52w;
    }
}

sub vcl_deliver {
    # Remove http header
    remove resp.http.X-Varnish;
    remove resp.http.x-url;
    remove resp.http.x-host;
}

# make the hash key simply the url
# default is url+(req.http.host|server.ip) ... both change depending on the request and we don't want that
sub vcl_hash {
    hash_data(req.url);
    return (hash);
}

