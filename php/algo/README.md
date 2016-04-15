# Algorithms folder

## Exploring area

One goal of Happn hacking is to bypass the _"must meet others to see them"_ limitation, and to display all the users in a given area of interest.

This folder contains some examples of algorithms to compute fake positions in order to cover that area.

* Rectangle bissect : the area is a rectangle, and the algorithm bissects it. To be efficient, the bissect level should be adjusted to the size of the rectangle, in order to explore below a distance of 500 m, that is the _"meeting radius"_ in Happn. This algorithm is best-suited for large area (but slow) exploration.

* Square spiral : the area is a square, first starting from the center and spiraling away, then going back to the center. This algorithm is best suited for quick exploration around a position (typically your), and mimics a path in real world that could deceive a possible fake position detection algorithm in Happn. Again, to be efficient, the spiral step should be below 500 m.

The HTML files demonstrate the algorithms by plotting the positions onto an HTML canvas with (x,y) coordinates. The PHP files replace the (x,y) flat coordinates by the (latitude, longitude) geo-coordinates and submit them to Happn in order to trigger the _'near you'_ notifications. You can find the geo-coordinates using Google Maps.

## Locating other users

Another goal would be to display the position of the other users.

Achieving this goal is quite difficult with Happn : not only the other users are always moving, but the server rounds the distance and makes the classical trilateration impossible, or at least very hard.

_To be refined and completed._
