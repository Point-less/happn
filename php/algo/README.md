# Algorithm folder

## Exploring area

One goal of Happn hacking is to bypass the _"must meet others to see them"_ limitation, and to display all the users in a given area of interest.

This folder contains some examples of algorithms to compute fake positions, that could be reused later in your application to set the position through the API.

These examples plot the positions onto a HTML canvas with (x,y) coordinates. Your application shall use (latitude, longitude) coordinates in place of (x,y) ones. You can get such coordinates with Google Maps.

* Rectangle bissect : the area is a rectangle, and the algorithm bissects it. To be efficient, the bissect level should be adjusted to the size of the rectangle, in order to explore below a distance of 500 m, that is the _"meeting radius"_ in Happn. This algorithm is best-suited for large area (but slow) exploration.

* Square spiral : the area is a square, first starting from the center and spiraling away, then going back to the center. This algorithm is best suited for quick exploration around a position (typically your), and mimics a path in real world that could deceive a possible fake position detection algorithm in Happn. Again, to be efficient, the spiral step should be below 500 m.

## Locating other users

_to be completed_

