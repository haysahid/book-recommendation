# Book Recommendation System

This project is a **Book Recommendation System** built using modern web technologies. It aims to provide book recommendations based on user search keywords. The system is underdevelopment and includes features for scraping book data, preprocessing it, and generating recommendations.

This project is developed as part of a study on recommendation systems, focusing on how content-based filtering techniques can be applied to provide personalized suggestions. The default target for scraping book data is [Gramedia](https://www.gramedia.com/), utilizing its publicly available API to fetch book information.

## Features

- Book recommendations.
- User-friendly interface for browsing and searching books.
- Integration with a database for storing book information.
- Using TF-IDF and Cosine Similarity for content-based filtering.

## Installation

Follow these steps to set up the project on your local machine:

1. **Clone the Repository**:
    ```bash
    git clone https://github.com/haysahid/book-recommendation.git
    cd book-recommendation
    ```

2. **Install Dependencies**:
    Ensure you have [Composer](https://getcomposer.org/) and [Node.js](https://nodejs.org/) installed. Then run:
    ```bash
    composer install
    npm install
    ```

3. **Set Up Environment**:
    Copy the `.env.example` file to `.env` and configure your database and other environment variables:
    ```bash
    cp .env.example .env
    ```

4. **Generate Application Key**:
    ```bash
    php artisan key:generate
    ```

5. **Run Migrations**:
    Set up the database schema:
    ```bash
    php artisan migrate
    ```

6. **Seed the Database** (Optional):
    Populate the database with sample data:
    ```bash
    php artisan db:seed
    ```

7. ** Build Frontend Assets**:
    Compile the frontend assets using:
    ```bash
    npm run dev
    ```

    or for production:
    ```bash
    npm run build
    ```

8. **Run the Application**:
    Start the development server:
    ```bash
    php artisan serve
    ```

    Open your browser and navigate to `http://localhost`.

## Usage
### Admin Login
To manage the system, log in as an admin using the default credentials:

- **Username**: `admin`
- **Password**: `admin2025`

Access the login page at [http://localhost/login](http://localhost/login). Once logged in, you can manage the system via the admin panel at [http://localhost/admin](http://localhost/admin).

Once logged in, you can add new books by scraping data, preprocessing it, and then using the recommendation system.

### Scraping Data
The system supports scraping book data from external sources (Gramedia). Use the built-in scraping tool:
1. Go to the "Scraping" section in the admin panel.
2. Start the scraping process and review the fetched data before saving it to the database.

### Preprocessing
Before generating recommendations, the system preprocesses data to ensure accuracy:
1. Navigate to the "Preprocessing" section.
2. Run the preprocessing tool to clean and structure the data.
3. Verify the processed data in the "Data Overview" section.

### Trying Out the Recommendation System
1. Browse the available books and interact with the system by searching for titles you like.
2. The system will provide recommendations based on your search keywords including its recommendation score.

## Contributing

Contributions are welcome! If you'd like to contribute, please fork the repository and submit a pull request. For major changes, please open an issue first to discuss what you would like to change.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
