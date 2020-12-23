from flask import (
    Blueprint, flash, redirect, render_template, request, session, url_for
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
                return redirect(url_for('video.host'))
            elif "joinroom" in request.form:
                return redirect(url_for('video.guest'))
        else:
            flash(error)

    return render_template('video/index.html')


@bp.route('/host', methods=('GET', 'POST'))
def host():
    return render_template('video/host.html')


@bp.route('/guest', methods=('GET', 'POST'))
def guest():
    return render_template('video/guest.html')
