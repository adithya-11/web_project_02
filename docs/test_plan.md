# Test plan

- Load the home and team pages with the documented local server.
- Verify PHP sessions persist between requests.
- Verify direct HTTP requests for data are denied on CODD.
- Register and sign in with valid credentials. Check duplicate usernames with different capitalizations.
- Confirm invalid credentials retain the username and show a general error.
- Confirm a missing CSRF token yields 403 and logout via GET yields 405.
- Verify a logged-out or expired session cannot reach the Entry Hall.
