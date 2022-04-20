# uploadcsv
Upload csv with books info


# Download project
```sh
$ git init
$ git remote add origin https://github.com/GoranSubic/uploadcsv
$ git pull
```

# Install vendor files
```sh
$ composer install
```
- Do you trust "slince/composer-registry-manager" to execute code and wish to enable it now?
  (writes "allow-plugins" to composer.json) [y,n,d,?]
  y

# First, make sure you install Yarn package manager.
- Optionally you can also install the  Node.js.
```sh
$ yarn install
```
- or if you use the npm package manager
```sh
$ npm install
```

- Create public files
```sh
$ yarn encore dev
```

# .env contains database setup info
- DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=5.7&charset=utf8mb4"

# Commands to create and update db
```sh
$ bin/console doctrine:database:create
$ bin/console doctrine:schema:update --force
```

- Command to delete db
```sh
$ bin/console doctrine:database:drop --force
```
