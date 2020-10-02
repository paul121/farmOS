# Authentication

farmOS includes an OAuth2 Authorization server for providing 1st and 3rd party
clients access to the farmOS API. Rather than using a user's username and
password to both *authorize and authenticate* a request, OAuth2 requires
users to complete an *authorization flow* that generates an `access_token`
to be used for authentication. Access tokens are provided to both 1st and
3rd party clients who wish to access the server's protected resources. Clients
store the `access token` instead of the user's credentials, which makes it a
more secure authentication method.

Read more about the [OAuth 2.0 standards].

## Client Libraries

The [farmOS.py] and [farmOS.js] client libraries use the OAuth2 protocol to
interact with the farmOS API.

## OAuth2 Bearer Tokens

Once you have an OAuth2 token, you can authenticate requests to the farmOS
server by including an `Authentication: Bearer {access_token}` header.

## OAuth2 Details

The OAuth protocol defines a process where users *authorize* 1st and 3rd
party *clients* with *scoped* access to data on the server. The following
describes the details necessary for using OAuth2 authorization with a farmOS
server.

### Scopes

OAuth Scopes define different levels of permission. The farmOS server
implements scopes as roles associated with OAuth clients. This means that users
will authorize clients with roles that determine how much access they have
to data on the server.

### Clients

An OAuth Client represents a 1st or 3rd party integration with the farmOS
server. Clients are uniquely identified by a `client_id` and are
configured to use different `scopes`.

The core `farm_api` module provides a default client with
`client_id = farm`. If you are writing a script that communicates with *your*
farmOS server via the API, you should use this client to authorize access and
generate an `access_token` for authentication.

If you are creating a third party integration with farmOS, see the [`farm_api
` module documentation](/module/farm_api) for steps to create an OAuth Client.

### Authorization Flows

The [OAuth 2.0 standards] outline 5 [Oauth2 Grant Types] to be used in an OAuth2
Authorization Flow - They are the *Authorization Code, Implicit, Password
Credentials, Client Credentials* and *Refresh Token* Grants. The
[Authorization Code](#authorization-code-grant) and
[Refresh Token](#refreshing-tokens) grants are the only Authorization Flows
recommended by farmOS for use with 3rd party clients.

**NOTE:** Only use the **Password Grant** if the client can be trusted with a
farmOS username and password (this is considered *1st party*). The
**Client Credentials Grant** is often used for machine authentication not
associated with a user account. The client credentials grant should only be
used if a `client_secret` can be kept secret. If connecting to multiple
farmOS servers, each server should use a different secret. This is
challenging due to the nature of farmOS being a self-hosted application.

#### Authorization Code Grant

The Authorization Code Grant is most popular for 3rd party client authorization.

Requesting resources is a four step process:

**First**: the client sends a request to the farmOS server `/oauth/authorize`
endpoint requesting an `Authorization Code`. The user logs in and authorizes
the client to have the OAuth Scopes it is requesting.

    Copy this link to browser -
    http://localhost/oauth/authorize?response_type=code&client_id=farmos_development&scope=user_access&redirect_uri=http://localhost/api/authorized&state=p4W8P5f7gJCIDbC1Mv78zHhlpJOidy

**Second**: after the user accepts, the server redirects
to the `redirect_uri` with an authorization `code` and `state` in the query
parameters.

    Example redirect url from server:
    http://localhost/api/authorized?code=9eb9442c7a2b011fd59617635cca5421cd089943&state=p4W8P5f7gJCIDbC1Mv78zHhlpJOidy

**Third**: copy the `code` and `state` from the URL into the body of a POST request.
The `grant_type`, `client_id`, `client_secret` and `redirect_uri` must also be
included in the POST body. The client makes a POST request to the
`/oauth/token` endpoint to retrieve an `access_token` and `refresh_token`.

    foo@bar:~$ curl -X POST -d "grant_type=authorization_code&code=ae4d1381cc67def1c10dc88a19af6ac30d7b5959&client_id=farmos_development&redirect_uri=http://localhost/api/authorized" http://localhost/oauth/token
    {"access_token":"3f9212c4a6656f1cd1304e47307927a7c224abb0","expires_in":"10","token_type":"Bearer","scope":"user_access","refresh_token":"292810b04d688bfb5c3cee28e45637ec8ef1dd9e"}

**Fourth**: the client sends the access token in the request header to access protected
resources. The header is an Authorization header with a Bearer token:
 `Authorization: Bearer access_token`

    foo@bar:~$ curl --header "Authorization: Bearer b872daf5827a75495c8194c6bfa4f90cf46c143e" http://localhost/farm.json
    {"name":"farmos-server","url":"http:\/\/localhost","api_version":"1.1","user":{"uid":"1","name":"admin", ....

#### Password Credentials Grant

**NOTE:** Only use the **Password Grant** if the client can be trusted with a
farmOS username and password (this is considered *1st party*).

The Password Credentials Grant uses a farmOS `username` and `password` to
retrieve an `access_token` and `refresh_token` in one step. For the user, this
is the simplest type of *authorization.* Because the client can be trusted with
their farmOS Credentials, a users `username` and `password` can be collected
directly into a login form within the client application. These credentials are
then used (not stored) to request tokens which are used for *authentication*
with the farmOS server and retrieving data.

Requesting protected resources is a two step process:

**First**, the client sends a POST request to the farmOS server `/oauth/token`
endpoint with `grant_type` set to `password` and a `username` and `password`
included in the request body.

    $ curl -X POST -d "grant_type=password&username=username&password=test&client_id=farm&scope=user_access" http://localhost/oauth/token
    {"access_token":"e69c60dea3f5c59c95863928fa6fb860d3506fe9","expires_in":"300","token_type":"Bearer","scope":"user_access","refresh_token":"cead7d46d18d74daea83f114bc0b512ec4cc31c3"}

**second**, the client sends the `access_token` in the request header to access protected
resources. The header is an Authorization header with a Bearer token:
 `Authorization: Bearer access_token`

    foo@bar:~$ curl --header "Authorization: Bearer e69c60dea3f5c59c95863928fa6fb860d3506fe9" http://localhost/farm.json
    {"name":"farmos-server","url":"http:\/\/localhost","api_version":"1.1","user":{"uid":"1","name":"admin", ....

#### Refreshing Tokens

The `refresh_token` can be used to retrieve a new `access_token` if the token
has expired.

It is a one step process:

The client sends an authenticated request to the `/oauth/token`endpoint with
`grant_type` set to `refresh_token` and includes the `refresh_token`,
`client_id` and `client_secret` in the request body.

    foo@bar:~$ curl -X POST -H 'Authorization: Bearer ad52c04d26c1002084501d28b59196996f0bd93f' -d 'refresh_token=52e7a0e12e8ddd08b155b3b3ee385687fef01664&grant_type=refresh_token&client_id=farmos_api_client&client_secret=client_secret' http://localhost/oauth/token
    {"access_token":"acdbfabb736e42aa301b50fdda95d6b7fd3e7e14","expires_in":"300","token_type":"Bearer","scope":"user_access","refresh_token":"b73f4744840498a26f43447d8cf755238bfd391a"}

The server responds with an `access_token` and `refresh_token` that can be used
in future requests. The previous `access_token` and `refresh_token` will no
longer work.

[OAuth 2.0 standards]: https://oauth.net/2/
[OAuth2 Grant Types]: https://oauth.net/2/grant-types/
[farmOS.py]: https://github.com/farmOS/farmOS.py
[farmOS.js]: https://github.com/farmOS/farmOS.js
