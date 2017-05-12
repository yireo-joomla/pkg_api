# API component for Joomla
With this component, you can serve JSON API feeds from simple calls. Each request is confirming to the following format:

    http://JOOMLA/ENTRY_POINT/COMPONENT/MODEL/1

The `ENTRY_POINT` is a Menu-Item that you create yourself and is appoint to the component. The `COMPONENT` is the component name - equal to `option` but without the `com_` part. The `MODEL` is the name of the model.

For instance, the following URL outputs the output of `com_content` its `articles` model (`ContentModelArticles`) using the method `getItems()`.

    http://localhost/joomla1/index.php/api/content/articles/
    
For singular items, the method `getItem()` could be used. If alternative methods, or alternative model-names are used, you will need to include an `api.php` file within your own component. See the `components/com_content/api.php` file in this repo for a dummy.
    
## Fun stuff
A single article:

    http://localhost/joomla1/index.php/api/content/articles/42
    
Get a list of users (if logged in as an admin user):

    http://localhost/joomla1/index.php/api/users/users

Finder search:

    http://localhost/joomla1/index.php/api/finder/suggestions?q=t