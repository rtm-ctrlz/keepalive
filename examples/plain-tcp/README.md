# Plain TCP examples

Following examples represents different cases:

- server
- client (sockets)
- client (stream)

Do not use them in conjunction - they will dead-lock each other, but will send keepalives ;)

# Server

With a netcat (`nc`) client - to be sure that server (not client) is sending keepalives.

```shell
# running tcpdump (to see packets)
sudo tcpdump -npAi any host 127.0.0.1 and port 9898 &

# running server
php server.php &
# delay (for server to start)
sleep 1

# dummy client
while sleep 10; do date; done | nc 127.0.0.1 9898

# don't forget to stop tcpdump
```

# Clients

With a netcat (`nc`) server - to be sure that client (not server) is sending keepalives.

```shell
# running tcpdump (to see packets)
sudo tcpdump -npAi any host 127.0.0.1 and port 9898 &

# run client with a delay
(sleep 1; php socket-client.php) &

# dummy server
while sleep 10; do date ; done | nc -l 127.0.0.1 9898

# don't forget to stop tcpdump
```
