import urllib.request
import json
import urllib.parse

import os

import googleapiclient.discovery
import googleapiclient.errors

from definitions import ROOT


def get_vid_info(vid_id):
    params = {"format": "json", "url": "https://www.youtube.com/watch?v=%s" % vid_id}
    url = "https://www.youtube.com/oembed"
    query_string = urllib.parse.urlencode(params)
    url = url + "?" + query_string

    with urllib.request.urlopen(url) as response:
        response_text = response.read()
        data = json.loads(response_text.decode())
    return data


def search(q, video_embeddable="true", video_syndicated="true", max_results=5):
    api_service_name = "youtube"
    api_version = "v3"

    # Note: this is (naturally) not committed to git, and must be populated by going to the Google API console manually
    # https://console.developers.google.com/apis/credentials
    # If no API calls have been made in 90 days, the access may expire.
    with open(os.path.join(ROOT, "secrets/apikey.txt"), "r") as infile:
        api_key = infile.read().strip()

    youtube = googleapiclient.discovery.build(
        api_service_name, api_version, developerKey=api_key)

    request = youtube.search().list(
        part="snippet",
        q=q,
        type="video",
        videoEmbeddable=video_embeddable,
        videoSyndicated=video_syndicated,
        maxResults=max_results,
    )
    response = request.execute()

    return response["items"]


if __name__ == "__main__":
    search("hello")
