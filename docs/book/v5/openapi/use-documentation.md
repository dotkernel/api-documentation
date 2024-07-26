# Using the documentation

Since Redoc is readonly, in the following section we will focus only on using Swagger UI.

## Protected endpoints

Now that you have a UI for the documentation, you can see all the endpoints. You will see that some of them have a lock
symbol right before the collapse/expand arrow. When you see this symbol next to an endpoint, it means that the endpoint
is protected and can only be accessed when authenticated with an account with proper permissions.

## Authentication

In Swagger UI, you will see an `Authorize` button. Clicking it will open a modal where you will find two sections:

- `AuthToken` - where you will have to enter a valid auth token
- `ErrorReportingToken` - where you will have to enter a valid error reporting token

Below, we will walk you through on how to find both tokens. For now, let's close the modal.

### Generating AuthToken

This token is required with most of the Dotkernel API endpoints. There are two entities that generate this type of
token: `(super)admin`s and `user`s. Depending on the endpoint description, you will know which one you need to use.
Examples:

- `/user`: the description says `Admin lists user accounts` - it means that you need an AccessToken with `(super)admin`
  privileges
- `/user/my-account`: the description says `User fetches their own account` - it means that you need an AccessToken with
  `user` privileges

In the UI, find a section called `AccessToken`, toggle the `/security/generate-token` (`Generate access token`) endpoint
and click the `Try it out` button. Under the `Access token generation request` you will find a textarea prepopulated
with a JSON object. You will have to change the value of `username` and `password`. See
[this guide](../tutorials/token-authentication.md#credentials) for the credentials.

After you have filled out the credentials, click on the `Execute` button below the textarea. This will send the request
to your instance of Dotkernel API. If everything went well, under the textarea you should see:
- the `curl` request that was made
- the `Request URL` the request was sent to
- the `Server response` with `200 OK` response code and the `Response body` with a JSON object containing `token_type`,
  `expires_in`, `access_token` and `refresh_token`.

> Save the `refresh_token` somewhere, you will need it later

Now copy the value of `access_token` (make sure you copy all the characters, without the surrounding double quotes) and
go back up to the `Authorize` button and click it to open the auth modal. Paste the copied token as the value of the
`AuthToken` and click on the **Authorize** button you see under the input field. The **Authorize** button has now
changed to **Logout**. You can close the modal.

From here, Swagger UI will remember the AuthToken until you close/refresh the browser tab. Also, it will automatically
append the `Authorization` header to each request, allowing you to make authorized API calls.

If you need to switch to an account with different privileges, you go again to the `Authorize` button, click on it to
open the auth modal, and click **Logout** for the `AuthToken`. Then paste the new token as the value of the `AuthToken`,
click on the **Authorize** button, close the modal and continue using the UI authenticated with the new account.

### Refreshing AuthToken

By default, auth tokens expire in 1 day. If you make an API call, and you receive an error telling you that your auth
token is expired, you need to either generate a new token (as seen above) or refresh the existing one using the
`refresh_token` received when generating the current token.

In order to refresh the auth token, you find the same section called `AccessToken`, toggle the `/security/refresh-token`
(`Refresh access token`) endpoint and click the `Try it out` button. Under the `Access token refresh request` you will
find a textarea prepopulated with a JSON object. You will have to change the value of `refresh_token` to the refresh
token of your current auth token.

Once done, click on the `Execute` button below the textarea. This will send the request to your instance of Dotkernel
API. If everything went well, under the textarea you should see the same details:
- the `curl` request that was made
- the `Request URL` the request was sent to
- the `Server response` with `200 OK` response code and the `Response body` with a JSON object containing `token_type`,
  `expires_in`, `access_token` and `refresh_token`.

From here, you will follow the same steps:
- copy the `access_token`
- go to the `Authorize` button to open the auth modal
- paste the new token and click on **Authorize**
- close the modal

### Generating ErrorReportingToken

Just like the AuthTokens, ErrorReportingTokens are used to make authorized API calls. The difference is that this token
applies only to one specific endpoint: `/error-report` (`Report an error to the API`). This endpoint is intended to be
used by third-party applications and frontends to report an error back to the API.

> This endpoint does not require `AuthTokens`

In order to generate this token, follow [this guide](../commands/generate-tokens.md#generate-error-reporting-token).

Once you have the error reporting token, go again to the `Authorize` button, paste the new token as the value of the
`ErrorReportingToken`, click on the **Authorize** button and close the modal. Now you're ready to report errors to your
instance of Dotkernel API.

## Making API calls

> The UI does not use confirmation messages before making an API call so double check any operation before executing it.

Once authorized in the UI, you can click on any endpoint to expand it. There you will find an overview of the endpoint, including:

- Request method (`DELETE`, `GET`, `PATCH`, `POST`, `PUT`)
- request URL (example: `/resource`)
- Short description
- Long description
- Parameters - if this area says `No parameters`, then there are no parameters to fill out; else, make sure you fill out
all the required parameters
- Request body - if present, provides a textarea prepopulated with a JSON object describing the request
- Responses - a list of possible HTTP status codes and their respective response bodies

Clicking the `Try it out` button will activate any parameter input fields and the request body textarea (if any).
Clicking `Cancel` will deactivate them.

Make sure you fill out all the necessary data, then click on the `Execute` found button above `Responses`. This will
send the request and return and display the API response. Once finished, you will see the response as the first item
under `Responses`, including the HTTP status code and the response body.

You can repeat the request by clicking again on the `Execute` button. This will first clear the previous output and
display the new response in the same place. Additionally, between two executions, you can manually clear any previous
output using the `Clear` button next to the `Execute` button.
