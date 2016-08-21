# Login-GroceryCrud
A Login/Logout system for GroceryCrud (codeigniter).

### Requirement
- Table 'crud_users'
- - field 'id' INT PRIMARY AUTOINCREMENT
- - field 'username'
- - field 'password'
- - (optional) field 'permissions'

Create a table in your mysql named "crud_users" with "username" and "password" fields.
If you want you can create a "permissions" field too, that can be used to get a value that will filter your crud tables based on that value, but you must create the system by yourself (I'll probably create an example for that).

### How to "Install"
There are 3 files:
- application/controllers/Login.php
- application/models/Login_model.php
- application/views/login.php

You can edit the views/login.php as you wish (but if you leave as is it's out-of-the-box responsive and working)
-AND-
you can look into the controllers/login.php file because on the top of the file there are a couple of configuration (and translations) than you can manage easily.

That's it.


### Advanced tools
A model file comes with the release and contains some basics stuff:
- isLogged() - Return true if the player is logged, false if is not (here you can add a redirect("/login") )
- logout() - log out the user and make an instant redirect to the login page
- permission() - return the user's permission field of the database or the username if the permission field doesn't exists
- permissions(), getPermission() and getPermissions() are aliases of permission()

TO USE IT you just need to load the library into your controllers files like that:
```
$this->load->model("Login_model");
```
and use it like:

```
if($this->login_model->isLogged()){
    echo "HI! You are Logged IN!";
}else{
    redirect("/login");
}
```

Pretty easy, uh?
