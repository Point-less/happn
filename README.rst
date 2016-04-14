#Happn APIs
Python / PHP modules for interacting with the Happn's REST API

##Python Module Installation

Download the source and run:
```
	python setup.py install
```

##What is included
```
	\happn 	- Source
	\docs	- Documentation of functions
	\bin	- Prebuilt scripts using python Happn API
        setHappnPosition.py - Script for setting user position
	\examples - exmaple implementations
```

##Getting Started
First you need a facebook token to create a Happn User-Object. You can get the one associated with your facebook account by clicking [here](https://www.facebook.com/dialog/oauth?client_id=247294518656661&redirect_uri=fbconnect://success&scope=public_profile&response_type=token) and copying it from the address bar.

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
    + Get conversations
    + Get messages
+ Add Scripts
    * Scripts not yet working
+ Test Sybil Locator
    + find [original](https://github.com/rickhousley/creepr/blob/master/happn/sybilSupriseDate.py) pre-api version here
