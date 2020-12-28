# Setup
### AWS
Following https://www.serverless.com/framework/docs/providers/aws/guide/credentials/#create-an-iam-user-and-access-key

* `npm install`

* Create a IAM user with minimum serverless policies

* Download the csv file containing the API key and secret key

* using aws-cli, `aws configure` and input the keys

### Python
Following the steps in https://www.serverless.com/blog/flask-python-rest-api-serverless-lambda-dynamodb

`cd flask`

`npm init -f`

`npm install --save-dev serverless-wsgi serverless-python-requirements`

`virtualenv venv --python=python3`

`source venv/bin/activate`

`sls deploy`

### Installing the YouTube API

* Navigate to https://console.developers.google.com/apis/credentials and find the YouTube API key created for the YPPT project
NOTE: If the API has not been used in 90 days, it will have expired, and a new project and API key will need to be created

* Paste the API Key in the secrets/apikey.txt file