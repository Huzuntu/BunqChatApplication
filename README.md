Backend Engineer Coding Assignment

I was asked to implement a chat application backend using PHP but I added some frontend to make it more understandable.

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

I tried to write a few tests, but since I am implementing an user interface (UI) for this project, you can see the responses in the UI.



Other than that you can contact me at umuttolekk@gmail.com or [Huzuntu](https://github.com/Huzuntu).


