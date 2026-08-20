# PeerLink-Transfer-Helper

Same Network transfer files

## Build

```
cd server
npm install
npx tsc  
node dist/server.js
```

run php

```
php -S 0.0.0.0:8000 -t public/
# if you wish to have a higher max upload size, use:
php -d upload_max_filesize=1024M -d post_max_size=1030M -S localhost:8000 -t public 
# modifly the max size according to your need
```

### ToDo

- [ ]  admin mode?
- [X]  clear font
- [ ]  light/dark mode
- [X]  make a queue for P2P
