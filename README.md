![alt text](images/enhavo.svg "enhavo")
<br/>
<br/>

enhavo-cli is a neat open source tool written for `bash` to handle common tasks in enhavo projects.


Contribute
----------

Help us to develop the software. 
Feel free to open tickets or pull requests or just give us feedback.
If you are a github user, you can star our project.

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
enhavo --self-update
```

Get started
-----------

Simply `cd` to your project root directory and type `enhavo` for further instructions. 

MIT License
-----------

License can be found [here](https://github.com/enhavo/enhavo/blob/master/LICENSE).
