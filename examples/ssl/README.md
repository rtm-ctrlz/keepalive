# SSL examples

### Files

- `dummy-client.php` - SSL echo client
- `dummy-server.php` - SSL echo server
- `keepalive-client.php` - SSL echo client with keepalive
- `keepalive-server.php` - SSL echo server with keepalive

### Usage 

Almost same as for `plain-tcp` examples.
Just using openssl as client instead netcat.

#### Server
```shell
# running tcpdump (to see packets)
sudo tcpdump -npAi any host 127.0.0.1 and port 9898 &

# running server
php keepalive-server.php &
# delay (for server to start)
sleep 1

# dummy client
while sleep 10; do date; done | openssl s_client -connect 127.0.0.1:9898

# don't forget to stop tcpdump
```


#### Client
```shell
# running tcpdump (to see packets)
sudo tcpdump -npAi any host 127.0.0.1 and port 9898 &

# run client with a delay
(sleep 1; php keepalive-client.php) & 

# dummy server
while sleep 10; do date ; done | openssl s_server -key ca.key -cert ca.pem -accept 9898 -pass pass:mypassword

# don't forget to stop tcpdump
```

