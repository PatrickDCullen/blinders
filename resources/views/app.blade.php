<!DOCTYPE html>
<html>
  <head>
    <title>Gmail API Quickstart</title>
    <meta charset="utf-8" />
  </head>
  <body>
    <p>Gmail API Quickstart</p>

    <!--Add buttons to initiate auth sequence and sign out-->
    <button id="authorize_button" onclick="handleAuthClick()">Authorize</button>
    <button id="signout_button" onclick="handleSignoutClick()">Sign Out</button>

    <input type="text" id="filter_input" placeholder="email">
    <button id="apply_filter_button" onclick="applyFilter()">Apply Filter</button>

    <pre id="filters_content" style="white-space: pre-wrap;"></pre>
    <pre id="labels_content" style="white-space: pre-wrap;"></pre>

    <script type="text/javascript">
      /* exported gapiLoaded */
      /* exported gisLoaded */
      /* exported handleAuthClick */
      /* exported handleSignoutClick */

      const CLIENT_ID = import.meta.env.VITE_GOOGLE_CLIENT_ID;
      const API_KEY = import.meta.env.VITE_GOOGLE_API_KEY;

      // Discovery doc URL for APIs used by the quickstart
      const DISCOVERY_DOC = 'https://www.googleapis.com/discovery/v1/apis/gmail/v1/rest';

      // Authorization scopes required by the API; multiple scopes can be
      // included, separated by spaces.
      const SCOPES = 'https://www.googleapis.com/auth/gmail.readonly';

      let tokenClient;
      let gapiInited = false;
      let gisInited = false;

      document.getElementById('authorize_button').style.visibility = 'hidden';
      document.getElementById('signout_button').style.visibility = 'hidden';
      document.getElementById('filter_input').style.visibility = 'hidden';
      document.getElementById('apply_filter_button').style.visibility = 'hidden';

      /**
       * Callback after api.js is loaded.
       */
      function gapiLoaded() {
        gapi.load('client', initializeGapiClient);
      }

      /**
       * Callback after the API client is loaded. Loads the
       * discovery doc to initialize the API.
       */
      async function initializeGapiClient() {
        await gapi.client.init({
          apiKey: API_KEY,
          discoveryDocs: [DISCOVERY_DOC],
        });
        gapiInited = true;
        maybeEnableButtons();
      }

      /**
       * Callback after Google Identity Services are loaded.
       */
      function gisLoaded() {
        tokenClient = google.accounts.oauth2.initTokenClient({
          client_id: CLIENT_ID,
          scope: SCOPES,
          callback: '', // defined later
        });
        gisInited = true;
        maybeEnableButtons();
      }

      /**
       * Enables user interaction after all libraries are loaded.
       */
      function maybeEnableButtons() {
        if (gapiInited && gisInited) {
          document.getElementById('authorize_button').style.visibility = 'visible';
        }
      }

      /**
       *  Sign in the user upon button click.
       */
      function handleAuthClick() {
        tokenClient.callback = async (resp) => {
          if (resp.error !== undefined) {
            throw (resp);
          }
          document.getElementById('signout_button').style.visibility = 'visible';
          document.getElementById('authorize_button').innerText = 'Refresh';
          document.getElementById('filter_input').style.visibility = 'visible';
          document.getElementById('apply_filter_button').style.visibility = 'visible';
          await listLabels();
        };

        if (gapi.client.getToken() === null) {
          // Prompt the user to select a Google Account and ask for consent to share their data
          // when establishing a new session.
          tokenClient.requestAccessToken({prompt: 'consent'});
        } else {
          // Skip display of account chooser and consent dialog for an existing session.
          tokenClient.requestAccessToken({prompt: ''});
        }
      }

      /**
       *  Sign out the user upon button click.
       */
      function handleSignoutClick() {
        const token = gapi.client.getToken();
        if (token !== null) {
          google.accounts.oauth2.revoke(token.access_token);
          gapi.client.setToken('');
          document.getElementById('labels_content').innerText = '';
          document.getElementById('filters_content').innerText = '';
          document.getElementById('filter_input').style.visibility = 'hidden';
          document.getElementById('apply_filter_button').style.visibility = 'hidden';
          document.getElementById('authorize_button').innerText = 'Authorize';
          document.getElementById('signout_button').style.visibility = 'hidden';
        }
      }

      /**
       * Print all Labels in the authorized user's inbox. If no labels
       * are found an appropriate message is printed.
       */
      async function listLabels() {
        let response;
        try {
          response = await gapi.client.gmail.users.labels.list({
            'userId': 'me',
          });
        } catch (err) {
          document.getElementById('labels_content').innerText = err.message;
          return;
        }
        const labels = response.result.labels;
        if (!labels || labels.length == 0) {
          document.getElementById('labels_content').innerText = 'No labels found.';
          return;
        }
        // Flatten to string to display
        const output = labels.reduce(
            (str, label) => `${str}${label.name}\n`,
            'Labels:\n');
        document.getElementById('labels_content').innerText = output;
      }

      async function applyFilter() {
        const filter = document.getElementById('filter_input').value;
        // await showFilters(filter);

        let response;
        try {
          response = await gapi.client.gmail.users.messages.list({
            'userId': 'me', 'maxResults': 1, 'labelIds' :["INBOX"], 'q':'from:' + filter
          });
        } catch (err) {
          document.getElementById('filters_content').innerText = err.message;
          return;
        }
        const emailId = response.result.messages[0].id;
        console.log("This is the emails response result in apply Filter");
        console.log(emailId);
        // if (!labels || labels.length == 0) {
        //   document.getElementById('filters_content').innerText = 'No labels found.';
        //   return;
        // }

        let response2;
        try {
            response2 = await gapi.client.gmail.users.messages.get({
                'userId': 'me', 'id': emailId
            });
        } catch (err) {
            document.getElementById('filters_content').innerText = err.message;
          return;
        }
        console.log("response2 result");
        console.log(response2.result);
        const emailMessage = response2.result.snippet;
        // Flatten to string to display
        // const output = labels.reduce(
        //     (str, label) => `${str}${label.name}\n`,
        //     'Labels:\n');
        const output = emailMessage;
        document.getElementById('filters_content').innerText = output;
      }


    </script>
    <script async defer src="https://apis.google.com/js/api.js" onload="gapiLoaded()"></script>
    <script async defer src="https://accounts.google.com/gsi/client" onload="gisLoaded()"></script>
  </body>
</html>
