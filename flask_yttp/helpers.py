import base36
from datetime import datetime
import random
import string

from flask import flash, redirect, url_for


def gen_room_code(replace_chars=3):
    base36_timestamp = base36.dumps(int(datetime.utcnow().timestamp() * 1000))
    # Replace the first 3 chars with random chars (0-9a-z), to prevent other users "guessing" other room codes
    # Since the room codes get periodically deleted, the first 2 chars are therefore not important as they will likely
    # be the same
    random_chars = ''.join(random.choice(string.ascii_lowercase + string.digits) for _ in range(replace_chars))
    room_code = random_chars + base36_timestamp[replace_chars:]
    return room_code


def flash_and_redirect(err_msg, redir="video.index"):
    flash(f"Error: {err_msg}")
    return redirect(url_for(redir))
