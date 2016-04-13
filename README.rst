#Happn API Python & PHP Modules
Modules in Python & PHP for interacting with Happn's REST API.

_Note: go to the `/php` subfolder for the PHP specific information._

##Installation

Download the source and run:
```bash
	python setup.py install
```

##What is included

```
  \happn                  - Source
  \docs                   - Documentation of functions
  \bin                    - Prebuilt scripts using python Happn API
    setHappnPosition.py   - Script for setting user position
  \examples               - Example implementations
  \php                    - PHP modules
```

##Getting Started

First you need a Facebook token to create (or reuse) a Happn user. Please look at the following wiki page to know how to get this token : https://github.com/rickhousley/happn/wiki/Happn-REST-API#facebook-authentication


```python
import happn
import pprint #For dictionary printing

token = <your facebook token>

# Generate the Happn User object
myUser = happn.User(token)

# Get user info of a specific user
targetUserDict = myUser.get_user_info(<target user id>)
pprint.pprint(targetUserDict)

# Set user position
myUser = myUser.set_position(20.0477203,-156.5052441) #Hawaii lat/lon

# Get recommendations
recs = myUser.get_recommendations()

# Like users
for rec in recs:
	relation = int(rec.get('notifier').get('my_relation'))
	if (relation == happn.Relations.none):
		user_id = int(rec.get('notifier').get('id'))
		myUser.like_user(user_id)
```

####Using the Scripts


####Using the API

##ToDo
+ Easier Setting Configuration
+ Decouple my settings, add to gitignore (decouple package)
+ Unimplemented API Calls
    + Charming a User
    + Send a message
    + Get conversations : _done in PHP, not in Python_
    + Get messages
+ Add Scripts
    * Scripts not yet working
+ Test Sybil Locator
    + find [original](https://github.com/rickhousley/creepr/blob/master/happn/sybilSupriseDate.py) pre-api version here
