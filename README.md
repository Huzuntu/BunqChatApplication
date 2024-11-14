# Backend Engineer Coding Assignment

I was asked to implement a chat application backend using PHP but I added some frontend to make it more understandable.
I tried to write a few tests, but since I am implementing an user interface (UI) for this project, you can see the responses in the UI.
Lastly, I know I could have had a better file/folder structure but for some reason I had problems when dealing with another folders in the project folder.
I tried to solve it but I couldn't do it. That is why I just wanted to be practical and write a working code. I tried to write as clean as possible.


I used Slim framework for routing as it was asked in the assignment. 

Features I added:
  - User Authentication: User can sign up, log in, and get authenticated using username and password.
  - Chat groups: User can see the list of chat groups and join them and send messages. All of the chat groups are public.
  - Chat history and messaging: User can see the chat history based on timestamps and usernames, also they can send new messages.
  - I added some templates and UI for better understanding.

Technologies:
  - PHP: Backend and Slim framework for routing
  - SQLite: Internal database for managing datas. It was asked in the assignment.
  - HTML/CSS/JavaScript: These are for frontend and simple UI

Setup Instructions
  
1.	Clone the Repository:
  	```
    git clone https://github.com/your-username/BunqChat.git
    cd BunqChat
    ```
2.	Install Dependencies:
    Make sure you install Composer, then run
  	```
    composer install
    ```
3.	Run the application:
    I added script:start to the composer.json, so you can start it by typing:
    ```
    composer start
    ```
    If this does not work somehow, this line will work and also you can change the ports to open more user sessions.
    ```
    php -S localhost:8080
    ```

I have added my chat.db SQLite database. If you want to create a database from scratch:
    ```
    
    CREATE TABLE Users (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      username TEXT NOT NULL,
      password TEXT NOT NULL
    );
    
    CREATE TABLE Groups (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      name TEXT NOT NULL,
    );
    
    CREATE TABLE Messages (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      group_id INTEGER NOT NULL,
      user_id INTEGER NOT NULL,
      content TEXT NOT NULL,
      timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY(group_id) REFERENCES Groups(id),
      FOREIGN KEY(user_id) REFERENCES Users(id)
    );
    


    







Other than that you can contact me at umuttolekk@gmail.com or [Huzuntu](https://github.com/Huzuntu).


