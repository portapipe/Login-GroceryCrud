# Login-GroceryCrud
A Login/Logout system for any CodeIgniter project (like GroceryCrud that don't need public user registration).

### Requirement
- MySQL
- CodeIgniter

It use an SQL table named "crud_users" with "username","password" and "permissions" fields. It will be created automagically if doesn't exists AND an admin user will be created too, so you can log in immediately!
The "permissions" field can store anything (VARCHAR 255) like a json_encode() array or just a keyword or a number, so you can manage the permissions of that user.

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
- logout(redirect=true) - log out the user and make an instant redirect to the login page (pass 'false' as argument to not redirect)
- permission() - return the user's permission field of the database or the username if the permission field doesn't exists
- permissions(), getPermission() and getPermissions() are aliases of permission()
- id() - return the user-id of the logged user
- name() username() - return the username of the user

TO USE IT you just need to load the library into your controllers files like that:
```
$this->load->model("Login_model");
```
and use it like:

```
if($this->login_model->isLogged()){
    $name = $this->login_model->name();
    echo "HI $name! You are Logged IN!";
}else{
    redirect("/login");
}
```

Pretty easy, uh?



### Example: Permissions for GroceryCrud
##### Note: I'm using some names like 'admin' or 'author' for the example but you can use really anything you want!
In this example you have a blog website. You need an "admin" user, an "author" user and a "revisioner" user.

The "admin" users will have any permission, so create the basic CRUD page with any permission you want to give them, like the ability to manage any user.
The "author" users can add and see their own articles, so we must show them just their work.
We can use a `$crud->where('author',$this->login_model->id())` and force the field 'author' in the add page to be the author's ID and non-editable by the user.
Finally the "revisioner" users can edit and delete any articles BUT can't add a new one, so we can unset the add button with `$crud->unset_add()` and we are done.

```
$permission = $this->login_model->permission();
if($permission=="admin"){
    echo "Hey, you're the boss, you can do anything!";
}

if($permission=="author"){
    $crud->where('author',$this->login_model->id());
    $crud->callback_before_insert(array($this,'useAuthorID'));
}
function useAuthorID($post_array) {
    $post_array['author'] = $this->login_model->id();
    return $post_array;
}   

if($permission=="revisioner"){
    $crud->unset_add();
}

if($permission==""){
    echo "I think you shouldn't be here...";
}
```
