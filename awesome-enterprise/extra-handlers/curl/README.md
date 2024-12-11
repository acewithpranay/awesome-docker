# curl-handler
provides support for using curl via shortcodes

You can use the following command to include it using the composer

`composer require wpoets/curl-handler`

supported shortcodes
1. curl.api.get
2. curl.api.post
3. curl.page.get
4. curl.api.patch

```
[template.set url_two='https://wpoets.com/api' /]
[arr.create o.set='template.data']
  [username _value='' /]
  [password _value='' /]
  [force_single_access]yes[/force_single_access]
[/arr.create]


[arr.create o.set='template.proxy']
        [host _value=''/]
        [port  _value=''/]
        [user  _value=''/]
        [password  _value=''/]   
[/arr.create]


[arr.create o.set='template.headers']
    [header new]    
        [key  _value=''/]
        [val  _value=''/]
    [/header]    
[/arr.create]


[curl.api.get url='{template.url_one}' o.set='template.urlone' c.ignore=t/]
[curl.api.get url='{template.url_two}' data='{template.data}' o.set='template.urltwo' c.ignore=t/]

[curl.api.post url='{template.url_two}' data='{template.data}' header='{template.headers}' proxy='{template.proxy}' o.set='template.urltwo' c.ignore=t/]
```

Support was also added to return cookies, with the response from v1.3 onwards.
