import csv
from pymongo.mongo_client import MongoClient


def insert_data(db, name, path, platform):
    mycol = db[name]
    with open(path, 'r', encoding='utf8') as file:
        csvreader = csv.reader(file)
        skip: bool = True
        for row in csvreader:
            if skip:
                skip = False
            else:
                entry = {
                    "type": row[1],
                    "title": row[2],
                    "director": row[3].split(', ') if row[3] != '' else None,
                    "actors": row[4].split(', ') if row[4] != '' else None,
                    "country": row[5],
                    "date_added": row[6],
                    "release_year": int(row[7]),
                    "rating": row[8],
                    "duration": int(row[9].split(' ')[0]) if row[9] != '' else None,
                    "genres": row[10].split(', ') if row[10] != '' else None,
                    "description": row[11],
                    "streaming_platform": platform
                }
                mycol.insert_one(entry)


def insert_directors(db, path, i, name):
    mycol = db[name]
    with open(path, 'r', encoding='utf8') as file:
        csvreader = csv.reader(file)
        skip: bool = True
        for row in csvreader:
            if skip:
                skip = False
            else:
                if row[i] != '':
                    entities = row[i].split(', ')
                    for entity in entities:
                        entry = {
                            "name": entity
                        }
                        print(entity)
                        if mycol.count_documents({'name': entity}) == 0:
                            mycol.insert_one(entry)


if __name__ == '__main__':
    client = MongoClient("mongodb://localhost:27017")
    mydb = client["MoXDB"]

    option: int = 1
    if option == 1:
        # Inserting data into movies
        insert_data(mydb, "movies", "C:/Users/YOSIF/Documents/GitHub/Proiect-Web-2024/src/api/db/netflix_titles.csv", "Netflix")
        insert_data(mydb, "movies", "C:/Users/YOSIF/Documents/GitHub/Proiect-Web-2024/src/api/db/disney_plus_titles.csv", "Disney")
    elif option == 2:
        # Inserting data into directors
        insert_directors(mydb, "C:/Users/YOSIF/Documents/GitHub/Proiect-Web-2024/src/api/db/netflix_titles.csv", 3, 'directors')
        insert_directors(mydb, "C:/Users/YOSIF/Documents/GitHub/Proiect-Web-2024/src/api/db/disney_plus_titles.csv", 3, 'directors')
    elif option == 3:
        # Inserting data into actors
        insert_directors(mydb, "C:/Users/YOSIF/Documents/GitHub/Proiect-Web-2024/src/api/db/netflix_titles.csv", 4, 'actors')
        insert_directors(mydb, "C:/Users/YOSIF/Documents/GitHub/Proiect-Web-2024/src/api/db/disney_plus_titles.csv", 4, 'actors')
    elif option == 4:
        # Inserting data into genres
        insert_directors(mydb, "C:/Users/YOSIF/Documents/GitHub/Proiect-Web-2024/src/api/db/netflix_titles.csv", 10, 'genres')
        insert_directors(mydb, "C:/Users/YOSIF/Documents/GitHub/Proiect-Web-2024/src/api/db/disney_plus_titles.csv", 10, 'genres')