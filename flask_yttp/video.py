from flask import (
    Blueprint, flash, redirect, render_template, request, session, url_for, g
)
from flask_yttp.helpers import gen_room_code, flash_and_redirect, get_err_msg
from flask_yttp.dynamo_calls import get_item_room, put_item_room, new_room_item
from flask_yttp.youtube_api_calls import get_vid_info, search

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
                new_room_code = gen_room_code()
                print(put_item_room(new_room_item(new_room_code)))
                session["room_id"] = new_room_code
                return redirect(url_for('video.host'))
            elif "joinroom" in request.form:
                room_input = request.form['room_input']

                if not room_input:
                    flash('Room code is required.')
                else:
                    session["guest_room_id"] = room_input.lower()
                    return redirect(url_for('video.guest'))
        else:
            flash(error)

    return render_template('video/index.html')


@bp.route('/host', methods=('GET', 'POST'))
def host():
    if "room_id" not in session:
        return flash_and_redirect("Room ID not in session cookie! Please enable cookies and retry")
    room_id = session["room_id"]
    room_id = "1"

    room_object = get_item_room(room_id)
    if not room_object:
        flash_and_redirect(f"Room {room_id} does not exist!")

    video_list = [pair["S"] for pair in room_object["videos"]["L"]]
    session["video_list"] = video_list
    print(f"Video List: {video_list}")

    session["vi"] = session["vi"] + 1 if "vi" in session else 0
    vi = session["vi"]
    if len(video_list) > vi:
        video_url = video_list[vi]
        session["videoURL"] = video_url
        video_info = get_vid_info(session["videoURL"])
        session["videoTitle"] = video_info["title"]
    next_vi = vi + 1
    if len(video_list) > next_vi:
        next_url = video_list[next_vi]
        session["nextURL"] = next_url
        next_video_info = get_vid_info(session["nextURL"])
        session["nextTitle"] = next_video_info["title"]
    else:
        session.pop("nextURL", None)

    return render_template('video/host.html')


@bp.route('/guest', methods=('GET', 'POST'))
def guest():
    try:
        if request.method == 'POST':
            search_query = request.form['search_query']
            error = None

            if not search_query:
                error = 'Search query is required.'

            if error is None:
                g.search_query = search_query
                if "search" in request.form:
                    search_results = search(search_query)
                    search_info_wanted = [{"title": res["snippet"]["title"], "vid": res["id"]["videoId"],
                                           "thumb": res["snippet"]["thumbnails"]["medium"]["url"]} for res in
                                          search_results]
                    g.search_info = search_info_wanted
            else:
                flash(error)
    except Exception as e:
        flash(get_err_msg(e))
    return render_template('video/guest.html')
