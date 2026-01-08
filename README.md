# Book Recommendation System

This repository contains a Book Recommendation System implemented with Laravel on the backend and a modern frontend stack. The project supports content-based recommendations (TF-IDF + Cosine Similarity) and model-based collaborative filtering for user-based recommendations using SVD (matrix factorization).

The system is intended for research and demonstration of recommendation techniques and includes tools for scraping, preprocessing, training, and serving recommendations.

## Features

-   Book management (CRUD): title, author, categories, ISBN, cover image.
-   Content-based recommendations using TF-IDF + Cosine Similarity on book metadata.
-   Collaborative Filtering (Model-Based) — User-Based filtering using SVD (matrix factorization).
-   Cold Start handling for new users (no transaction history): popularity fallback (recommend books ranked by transaction count or rating counts).
-   Reviews and ratings; user interactions are stored and used by recommendation models.
-   Transaction flow: cart, checkout, invoices, transaction items.
-   Data export to Excel/CSV (books, transactions, users).
-   Payment gateway and shipping integrations (repository/adapter pattern).
-   Admin dashboard: analytics, scraping tools, preprocessing utilities, tools for training and tuning the Collaborative Filtering model, and configuration for auto-training when data changes.
-   Background jobs / queue system for auto-training model.
-   Authentication and authorization (roles & permissions).

Note: The default target for scraping book data is [Gramedia](https://www.gramedia.com/), utilizing its publicly available API to fetch book information.

## Collaborative Filtering (Model-Based) — SVD Matrix Factorization

This project implements a model-based collaborative filtering pipeline using matrix factorization (SVD-style) for user-based recommendations. The model learns latent factors for users and items from historical interactions (such as ratings and purchases) to predict scores for unseen items.

**Core steps:**

1. Collect and aggregate user interaction signals (ratings, purchases).
2. Build a sparse user-item interaction matrix.
3. Train an SVD or matrix factorization model.
4. Save the trained model (embeddings or factor matrices) to disk or database for inference.
5. For each user, compute predicted scores for candidate items and return the top-N recommendations.

**Cold Start Handling:**

-   For users with no interaction history, the system falls back to popularity-based recommendations. Popularity is determined by the total number of transactions or ratings for each book. The top-N most popular books are returned as fallback recommendations. No onboarding questionnaire, hybrid reranking, or demographic personalization is applied for cold-start users.

**Implementation Notes:**

-   The Admin dashboard provides tools for manually training and tuning the collaborative filtering model, as well as options to configure auto-training triggers when book, transaction, or rating data changes.
-   `RecommendationSystemRepository` manages retrieval of model artifacts and generates recommendation results.
-   When using the Model Training Microservice, model artifacts are managed and persisted by the microservice itself. The main application interacts with the microservice via API to trigger training, fetch model artifacts, and update recommendations. Adjust integration settings in your `.env` file (`RECOMMENDATION_SYSTEM_API_URL`).

### Model Training Microservice Integration

A dedicated Model Training Microservice is available at [https://github.com/haysahid/book-reco-model](https://github.com/haysahid/book-reco-model). This microservice handles model training, tuning, model history management, and model serving in a decoupled and centralized manner.

When integrated, the Admin dashboard and background jobs (such as `TrainModel.php`) can trigger training, fetch model artifacts, and update recommendations by communicating with the microservice via API endpoints. This ensures that model lifecycle management is consistent and centralized, while the main application focuses on serving recommendations and user interactions.

## Installation

1. Clone the repository:

    ```bash
    git clone https://github.com/haysahid/book-recommendation.git
    cd book-recommendation
    ```

2. Install dependencies:

    ```bash
    composer install
    npm install
    ```

3. Environment setup:

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

    Configure the following in your `.env` file:

    - **Database credentials** and queue/driver settings.
    - **Midtrans keys** for payment integration:
        ```
        MIDTRANS_SERVER_KEY=your_midtrans_server_key
        MIDTRANS_CLIENT_KEY=your_midtrans_client_key
        ```
    - **RajaOngkir API key** for shipping:
        ```
        RAJAONGKIR_API_KEY=your_rajaongkir_api_key
        ```
    - **Model Training Microservice API URL**:
        ```
        RECOMMENDATION_SYSTEM_API_URL=https://your-model-microservice-url
        ```

4. Database migrations and seeding (optional):

    ```bash
    php artisan migrate
    php artisan db:seed
    ```

5. Start queue worker (recommended for model auto-training jobs):

    ```bash
    php artisan queue:work
    ```

6. Build frontend assets and run the application:

    ```bash
    npm run dev
    php artisan serve
    ```

    Open `http://localhost` in your browser.

7. For Model Training Microservice, clone and set it up from [https://github.com/haysahid/book-reco-model](https://github.com/haysahid/book-reco-model).

## Usage

-   Admin login: if seeded, default admin credentials may be present (for development seeds). Otherwise create an admin user via seeders or tinker.
-   Scraping: use the Admin -> Scraping panel to fetch and review book data before saving.
-   Preprocessing: run the preprocessing utilities from the Admin panel to normalize metadata used by content-based models.
-   Training recommendations: use the Admin -> Model Training panel to manually trigger training or configure auto-training.

## Example Recommendation Flow

1. User interacts with the site (searches, views, purchases, rates).
2. Interaction events are stored and optionally queued for batch aggregation.
3. The training job consumes aggregated interactions and updates model artifacts.
4. When a recommendation request arrives, the repository loads the model and returns top-N items.
5. If the user is new (cold start), fallback strategies are applied (popularity, onboarding, hybrid).

## Contributing

Contributions are welcome. Suggested workflow:

1. Fork the repository.
2. Create a feature branch: `git checkout -b feature/your-feature`.
3. Commit changes and open a pull request. For larger changes, open an issue first to discuss design.

## License

This project is licensed under the MIT License.
