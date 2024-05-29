# Content Negotiation

**Content Negotiation** is performed by an application in order to :

- To match the requested representation as specified by the client via the Accept header with a representation the
  application can deliver.
- To determine the `Content-Type` of incoming data and deserialize it so the application can utilize it.

Essentially, content negotiation is the *client* telling the server what it is sending and what it wants in return, and
the server determining if it can do what the client requests.
