![alt text](images/enhavo.svg "enhavo")
<br/>
<br/>

enhavo-cli is a neat open source tool written for `bash` to handle common tasks in enhavo projects.


Install
-------

To install enhavo-cli, just open a `bash`-terminal and type:

```bash
sudo wget https://github.com/enhavo/enhavo-cli/releases/latest/download/enhavo.phar -O /usr/local/bin/enhavo \
&& sudo chmod +x /usr/local/bin/enhavo
```

Or if you do not have `wget` try to install it with `curl`:

```bash
sudo curl -L https://github.com/enhavo/enhavo-cli/releases/latest/download/enhavo.phar --output /usr/local/bin/enhavo \
&& sudo chmod +x /usr/local/bin/enhavo
```

Update
-------
Once installed you can update enhavo-cli like so:

```bash
sudo enhavo self-update
```

Get started
-----------

Simply `cd` to your project root directory and type `enhavo` for further instructions. 

Configuration
-------------

Save your configuration under `.enhavo/config.yaml`. Or use
```
enhavo create-config
```

Here is an example file
```yaml
defaults:
  env: # define all your env vars that should used in your .env.local
    APP_ENV: dev
    MAILER_DSN: smtp://user:password@host.tld
    MAILER_FROM: "no-reply@host.tld"
    MAILER_TO: "me@host.tld"
    MAILER_NAME: "enhavo"
    MAILER_DELIVERY_ADDRESS: "test@host.tld"

  # define your default user that should be created
  user_email: 'me@host.tld' 
  user_password: 'mySecretPassword'

  # define your default database
  database_user: 'root'
  database_password: 'rootPassword'
  database_host: 'localhost'
  database_port: '3306'

# define your main repositories for vendor-symlink features
main_repositories:
  enhavo: /path/to/enhavo
```

Contribute
----------

Help us to develop the software. 
Feel free to open tickets or pull requests or just give us feedback.
If you are a github user, you can star our project.

MIT License
-----------

License can be found [here](https://github.com/enhavo/enhavo/blob/master/LICENSE).
