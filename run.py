from flask import Flask, request, jsonify
import instaloader
import requests
from urllib.parse import urlparse

app = Flask(__name__)

def get_instagram_username(shortcode):
    loader = instaloader.Instaloader()
    try:
        # Fetch the post details using the shortcode
        post = instaloader.Post.from_shortcode(loader.context, shortcode)
        return {
            "status": "success",
            "username": post.owner_username
        }
    except instaloader.exceptions.ProfileNotExistsException:
        return {"status": "error", "message": "Post not found or might be private."}
    except instaloader.exceptions.InstaloaderException as e:
        return {"status": "error", "message": str(e)}

def extract_shortcode_from_url(url):
    # Parse the URL and remove the query parameters
    parsed_url = urlparse(url)
    url_without_params = parsed_url.scheme + "://" + parsed_url.netloc + parsed_url.path

    # Check if the URL contains "/p/" or "/reel/" and extract the shortcode accordingly
    if "/p/" in url_without_params:
        segments = url_without_params.split('/p/')
    elif "/reel/" in url_without_params:
        segments = url_without_params.split('/reel/')
    else:
        return None

    # Remove trailing slashes and query parameters from the shortcode
    shortcode = segments[-1].split('?')[0].rstrip('/')
    return shortcode

@app.route('/instagram', methods=['GET'])
def fetch_instagram_username():
    url = request.args.get('url', '')

    if not (url.startswith('https://www.instagram.com/p/') or url.startswith('https://www.instagram.com/reel/')):
        return jsonify({"status": "error", "message": "Invalid Instagram URL."})

    shortcode = extract_shortcode_from_url(url)
    if not shortcode:
        return jsonify({"status": "error", "message": "Invalid Instagram URL format."})

    response = get_instagram_username(shortcode)
    if response["status"] == "error" and "Post not found or might be private." in response["message"]:
        return jsonify({"status": "error", "message": "The provided Instagram post URL is private."})
    return jsonify(response)

if __name__ == '__main__':
    app.run(debug=True)
