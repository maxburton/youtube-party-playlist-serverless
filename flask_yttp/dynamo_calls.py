import os
import boto3

env_roomtable_name = "ROOM_TABLE"
ROOM_TABLENAME = os.environ[env_roomtable_name] if env_roomtable_name in os.environ else "rooms"
client = boto3.client('dynamodb')


def get_item(tablename, pk_label, pk_id, pk_type="S"):
    resp = client.get_item(
        TableName=tablename,
        Key={
            pk_label: {pk_type: pk_id}
        }
    )
    table_item = resp.get('Item')
    return table_item


def get_item_room(room_id):
    return get_item(ROOM_TABLENAME, "room_id", room_id)


def put_item(tablename, item):
    return client.put_item(
        TableName=tablename,
        Item=item,
        ReturnConsumedCapacity='TOTAL',
    )


def put_item_room(new_item):
    return put_item(ROOM_TABLENAME, new_item)


def new_room_item(room_id):
    return {
        "room_id": {
            "S": room_id,
        },
        "videos": {
            "L": []
        }
    }
