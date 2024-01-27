## Technical Documentation

- ## Overview of project structure
  - standard for a Laravel project
- ## Key technologies
  - Laravel, Tailwind, AlpineJs, MySQL
- ## Installation and configuration
  - composer install
  - Setting environment variables - copy the env_example file to .env and fill in the appropriate values for your setup.
    - fill in the app name
    - artisan key:generate
    - fill in the database connection info
    - composer install
    - run migrations
    - seed initial data using file prasso_initial.sql
- ## Deployment
  - Hosting options - Current use is from AWS and a hosted EC2 instance. 
  - CI/CD workflows - Not yet implemented.
- ## API reference
  - Endpoints, parameters, responses
- Customization
  - ## Extending core functionality
- [Contributing guidelines](/docs/contributing.md) 





## Component Docs

- ## [Laravel Backend](https://github.com/laravel/laravel)
  - Framework overview
  - Custom controllers
  - Database schema - Contact the hosting developer to arrange this.
- ## Blade Templates Frontend
  - Component architecture
  - App/Site management
  - Page routing
- ## Firebase Integration

        Firebase provides user authentication and database storage for Prasso apps. 

        To integrate Firebase:

        ### 1. Create Firebase project

        Go to the Firebase console and create a new project. This will initialize Firebase for your Prasso app.

        ### 2. Add Firebase to app

        - Register the Prasso mobile app in the Firebase project by adding its bundle ID. This links the app to Firebase.

        - Download the GoogleService-Info.plist file. This contains credentials for the iOS app to connect to Firebase.

        - Follow the Firebase workflow to add the SDK to the Xcode project.

        ### 3. Enable authentication 

        In the Firebase console:

        - Enable Email/Password authentication in the Sign-in Method tab.

        - Enable user creation under the Users and Permissions tab. 

        This allows new user registration directly from the mobile app.

        ### 4. Add Cloud Firestore

        Under Develop > Database, add Cloud Firestore to the project. Set up security rules to control data access.

        ### 5. Add API key 

        Under Project Settings > General, register the Prasso API server and get an API key. 

        Use this key to allow the API backend to interact with Firebase services like authentication.

        Let me know if you would like any part of the Firebase integration setup covered in more detail!

- ## Mobile App
  - Built with Flutter [prasso_app](https://github.com/prasso/prasso_app)

- ## Media Services
  - Setup for Livestream and Podcast at AWS:  https://aws.amazon.com/blogs/media/awse-using-amazon-ivs-and-mediaconvert-in-a-post-processing-workflow/
    -  import json
import boto3
s3 = boto3.resource('s3')

## Pull your specific MediaConvert endpoint https://docs.aws.amazon.com/mediaconvert/latest/apireference/aws-cli.html

mediaconvert_client = boto3.client('mediaconvert', endpoint_url='https://q25wbt2lc.mediaconvert.us-east-1.amazonaws.com')


def getIVSManifest(bucket_name, prefix_name):
    object_path = "{}/events/recording-ended.json".format(prefix_name)
    object = s3.Object(bucket_name, object_path)
    body = str(object.get()['Body'].read().decode('utf-8'))
    metadata = json.loads(body)
    media_path = metadata["media"]["hls"]["path"]
    main_manifest = bucket_name + "/" + prefix_name +  "/" + media_path + '/' +metadata["media"]["hls"]["playlist"]
    print(main_manifest)
    return main_manifest
    
def createMediaConvertJob(manifest):
    ##Note change the following variables to your own, 
    role_arn = "arn:aws:iam::629811581977:role/service-role/mediaconvert_ivs_workflow-role-bzr2h0j0"
    preroll_path = "s3://faith-baptist-livestream/hls/faithbaptistonline/2023/2/12/hls/preroll.mp4"
    postroll_path = "s3://faith-baptist-livestream/hls/faithbaptistonline/2023/2/12/mp4/postroll.mp4"
    previously_recorded_image = "s3://faith-baptist-livestream/hls/faithbaptistonline/2023/2/12/thumbnails/thumb19.jpg"
    job_template = "mediaconvert_ivs_workflow"

    
    #### No edits needed past this point 
    
    settings_json = """{
   "Inputs": [
      {
 
        "TimecodeSource": "ZEROBASED",
        "VideoSelector": {
          "ColorSpace": "FOLLOW",
          "Rotate": "DEGREE_0",
          "AlphaBehavior": "DISCARD"
        },
        "AudioSelectors": {
          "Audio Selector 1": {
            "Offset": 0,
            "DefaultSelection": "DEFAULT",
            "ProgramSelection": 1
          }
        },
        "FileInput": "%s"
      },
      {
        "TimecodeSource": "ZEROBASED",
        "VideoSelector": {
          "ColorSpace": "FOLLOW",
          "Rotate": "DEGREE_0",
          "AlphaBehavior": "DISCARD"
        },
        "AudioSelectors": {
          "Audio Selector 1": {
            "Offset": 0,
            "DefaultSelection": "DEFAULT",
            "ProgramSelection": 1
          }
        },
        "FileInput": "s3://%s",
        "ImageInserter": {
          "InsertableImages": [
            {
              "Opacity": 75,
              "ImageInserterInput": "%s",
              "Layer": 0,
              "ImageX": 0,
              "ImageY": 0,
              "FadeIn": 5000,
              "FadeOut": 5000
            }
          ]
        }
      },
      {
        "TimecodeSource": "ZEROBASED",
        "VideoSelector": {
          "ColorSpace": "FOLLOW",
          "Rotate": "DEGREE_0",
          "AlphaBehavior": "DISCARD"
        },
        "AudioSelectors": {
          "Audio Selector 1": {
            "Offset": 0,
            "DefaultSelection": "DEFAULT",
            "ProgramSelection": 1
          }
        },
        "FileInput": "%s"
      }
    ]}""" % (preroll_path,manifest,previously_recorded_image,postroll_path)
    
    response = mediaconvert_client.create_job(JobTemplate=job_template,Role=role_arn,Settings=json.loads(settings_json))
    print(response)

def lambda_handler(event, context):
    print(json.dumps(event))
    prefix_name = event["detail"]["recording_s3_key_prefix"]
    bucket_name = event["detail"]["recording_s3_bucket_name"]

    #Get path to recording_ended.json and then create a MediaConvert job 
    createMediaConvertJob(getIVSManifest(bucket_name, prefix_name))

    return {
        'statusCode': 200,
        'body': ("Job created")
    }
    
