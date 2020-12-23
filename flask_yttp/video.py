from flask import (
    Blueprint, flash, redirect, render_template, request, session, url_for, jsonify
)

bp = Blueprint('video', __name__)


@bp.route('/', methods=('GET', 'POST'))
def index():
    if request.method == 'POST':
        name = request.form['name']
        error = None

        if not name:
            error = 'Name is required.'

        if error is None:
            session["name"] = name
            if "makeroom" in request.form:
                session["roomId"] = 1
                return redirect(url_for('video.host'))
            elif "joinroom" in request.form:
                return redirect(url_for('video.guest'))
        else:
            flash(error)

    return render_template('video/index.html')

import os
import boto3

ROOM_TABLENAME = "rooms"
client = boto3.client('dynamodb')


@bp.route('/host', methods=('GET', 'POST'))
def host():
    room_id = "1"
    resp = client.get_item(
        TableName=ROOM_TABLENAME,
        Key={
            'RoomId': {'N': room_id}
        }
    )
    room_object = resp.get('Item')
    if not room_object:
        flash(f"Error: Room {room_id} does not exist")
        return redirect(url_for('video.index'))

    video_list = room_object["Videos"]["L"]
    print(video_list)

    return render_template('video/host.html')


@bp.route('/guest', methods=('GET', 'POST'))
def guest():
    return render_template('video/guest.html')
