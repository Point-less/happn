#Happn APIs

Python / PHP modules for interacting with the Happn's REST API.

_Note: the following information is related to the Python modules. Please go to the `/php` subfolder for the PHP modules, and to the Wiki for information about the Happn application & API._


##What is included

```
  \happn                  - Source
  \bin                    - Prebuilt scripts using python Happn API
    setHappnPosition.py   - Script for setting user position
  \examples               - Example implementations
  \php                    - PHP modules
```

##Module Installation

Download the source and run:
```bash
	python setup.py install
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

##ToDo list
- [] Easier Setting Configuration
- [] Decouple my settings, add to gitignore (decouple package)
- Unimplemented API Calls
  * [] Charming a User
  * [] Send a message
  * [X] Get conversations : _done in PHP, remains to do in Python_
  * [] Get messages
- [] Add Scripts
  * Scripts not yet working
- [] Test Sybil Locator
  * find [original](https://github.com/rickhousley/creepr/blob/master/happn/sybilSupriseDate.py) pre-api version here
