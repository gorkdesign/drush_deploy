# sync_via_http.drush.inc

## Installation

Get a copy of [sync_via_http.drush.inc](http://api.drush.org/api/drush/examples!sync_via_http.drush.inc/master
https://github.com/drush-ops/drush/blob/7.x/examples/sync_via_http.drush.inc). It is included in the examples folder of the drush installation. Move or copy it to the ~/.drush/ folder.

## Drush Site Aliases Configuration
Setup your drush alias on the server where you want to allow the sql dumps to be downloaded from per the instructions in the link above. Here is a section from a drush alias file. The bold section is what is needed.

```javascript
 $aliases['test'] = array(
  'root' => '/home/clients/websites/w_username/public_html/environmentname',
  'path-aliases' => array(
    '%files' => 'sites/default/files',
    '%dump-dir' => 'dbouthouse',
    ),
  'source-command-specific' => array( // This source-command-specific array sets the URL, username and password that will allow a remote developer to retrieve the database dump via HTTP.
  'sql-sync'  => array(
    'http-sync'  => 'http://environmentname.dev6.webenabled.net/dbouthouse/my-database-dump.sql',
    'http-sync-user' => 'DBdude392',
    'http-sync-password' => 'SuperSecurePassword',
    ),
  ),
  'target-command-specific' => array(
    'sql-sync' => array(
//      'sanitize' => TRUE, // Use this if you want sensitive information sanitize in the database dump.
//      'confirm-sanitizations' => TRUE, // Use this if you want to confirm whether the sanitation should be preformed each time.
      'no-ordered-dump' => TRUE,
      'no-cache' => TRUE,
    ),
  'command-specific' => array( // This command-specific array sets the file the sql-dump should be placed in.
  'sql-dump' => array(
    'result-file' => '/home/clients/websites/w_username/public_html/deploy/shared/dbouthouse/my-database-dump.sql',
    'ordered-dump' => FALSE,
),
  ),
);
```
## Avoid Access Denied Error Messages
Create the folder for the database dump and add an empty **index.html** so a blank page is shown instead of an access denied error message.

To test create a  sql dump in the folder with the name given in the alias file with something like this:

```bash
drush sql-dump --gzip --result-file=dbouthouse/my-database-dump.sql
```

## Drush Shell Alias - Sync from live to dev, sanitize (optional) and dump
You can also created a Drush Shell Alias to sync the database from the live to the test environment and then dump it to a file on the test server.

### Sanitize database while dumping
If you want the database sanitize, before dumping it to the file to be downloaded, you can set this in your Drush Site aliases file.
```javascript
'target-command-specific' => array(
    'sql-sync' => array(
      'sanitize' => TRUE, // Use this if you want sensitive information sanitize in the database dump.
      'confirm-sanitizations' => TRUE, // Use this if you want to confirm whether the sanitation should be preformed each time.
      'no-ordered-dump' => TRUE,
      'no-cache' => TRUE,
    ),
  ),
```

### Drush Shell Alias
To create a Drush Shell Alias, in your ~/.drush/drushrc.php add a line something like the following. ie. For Drush Site Aliases **@mywebsite.test** and **@mywebsite.live**
```bash
$options['shell-aliases']['mywebsitesyncdbtotest'] = '! drush --yes sql-sync @mywebsite.live @mywebsite.test --create-db && drush --yes @mywebsite.test sql-dump';
```
If you have sanitation turned on you will need to set the Drupal user 1 password as it will have been wiped. You can use a Drush Shell Alias like this instead to be prompted to set the password.
```bash
$options['shell-aliases']['mywebsitesyncdbtotest'] = '! drush --yes sql-sync @mywebsite.live @mywebsite.test --create-db && drush --yes @mywebsite.test sql-dump && drush @mywebsite.test user-password "admin" --password=';
```


## Secure the database dump file
We need to protect the directory where the database dump file is with a username and password. Create a .htaccess file with something like this:

```apache
AuthUserFile /home/clients/websites/w_username/public_html/deploy/shared/.htpasswd
AuthName "MEOS Web Development"
AuthType Basic
<FilesMatch "\.(sql|gz)$">
order allow,deny
require valid-user
</FilesMatch>
RewriteEngine On
Satisfy Any
```

Create a .htpasswd file at the location specified in the .htaccess file then use a .htpasswd generator to md5 encrypt the passwords.

[David Walsh - Development Tools, including - **.htpasswd Username & Password Generator**](https://davidwalsh.name/web-development-tools)

**More Info:**

[Password Protect a Directory Using .htaccess By David Walsh on April 18, 2008](https://davidwalsh.name/password-protect-directory-using-htaccess)

[The RewriteEngine On Satisfy Any is from: Drupal’s default .htaccess file breaks webroot authentication](https://community.letsencrypt.org/t/drupals-defualt-htaccess-file-breaks-webroot-authentication/3014/2)

[Using FilesMatch and Files in .htacces](http://www.askapache.com/htaccess/using-filesmatch-and-files-in-htaccess)

## How to access the file

We need to pass in the http login info from the drush aliases file.

### Access via Drush Site Aliases file
```javascript
'source-command-specific' => array(
 'sql-sync'  => array(
   'http-sync'  => 'http://environmentname.dev6.webenabled.net/dbouthouse/my-database-dump.sql',
   'http-sync-user' => 'DBdude392',
   'http-sync-password' => 'SuperSecurePassword',
   ),
 ),
 ```
The easiest way, having the code something like the above in a Drush Site alias  and  then using it as the source for a drush sync will cause the file to be downloaded via http and imported. **@mywebsite.test** is the site in our example where the database dump can be downloaded from. So running the following to down load it would download the database via http. Note: The username and password in the Drush Site Aliases file is not setting the user and password, it is setting what username and password will be used for the download.

```bash
drush --yes sql-sync @mywebsite.test @mywebsite.dev
```

### Via a web browser

[Can you pass user/pass for HTTP Basic Authentication in URL parameters?](http://serverfault.com/a/371918)

Use a special URL format, like this:

http://username:password@example.com/

This sends the credentials in the standard HTTP "Authorization" header.

So in our case the url is:
http://DBdude392:SuperSecurePassword@environmentname.dev6.webenabled.net/dbouthouse/my-database-dump.sql

### Via wget
The database dump can also be fetched with wget using the options --user=user  and --password=password

```bash
wget --user=DBdude392 --password=SuperSecurePassword http://environment.dev6.webenabled.net/dbouthouse/my-database-dump.sql.gz
```

### Via curl
Via curl using the -u, --user <user:password;options>  and -o, --output file options.

```bash
curl -u DBdude392:SuperSecurePassword -o my-database-dump.sql.gz http://environmentname.dev6.webenabled.net/dbouthouse/my-database-dump.sql.gz
```


**IMPORTANT NOTE**: This example does not cause the sql dump to be performed. It is presumed that the dump file already exists at the provided URL. 

See the [Drush Shell Alias](#drush-shell-alias) section above for how to setup Drush Shell Alias to automate this in drush.

For a full web-enabled self-service solution, a web page that initiated an sql-dump (or perhaps a local sql-sync followed by an sql-sanitize and then an sql-dump) would be necessary.

