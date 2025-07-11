# Social Media Handle Discovery – Technical Challenge

This project is a **proof-of-concept** solution to a Social Media Discovery challenge, where the goal is to **generate
and identify possible social media handles** for a company using only its **name** and **website URL** as input.

The task goes beyond obvious guesses and includes:

- Discovering region-specific handles (e.g., `@StarbucksCanada`)
- Identifying brands/subsidiaries (e.g., `@Xbox` under Microsoft)
- Considering product profiles and fan-created handles
- Structuring and semantically enriching the discovery process

---

## 🚀 Setup & Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-username/laravel-openai-integration.git
   cd laravel-openai-integration
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install && npm run dev
   ```

3. **Configure `.env`**
   ```env
   GEMINI_API_KEY=your-gemini-api-key
   OPENAI_API_KEY=your-openai-api-key
   
   #Update your database connections
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=social_media_discovery
   DB_USERNAME=root
   DB_PASSWORD=password
   ```

4. **Key Generate & Run migrations**
   ```bash
   php artisan key:generate && php artisan migrate
   ```

5. **Queue worker**
   ```bash
   php artisan queue:work
   ```

6. **Serve the app**
   ```bash
   php artisan serve
   ```

[🔗 **Postman Collection
**:](https://www.postman.com/docking-module-architect-45770649/public-space/request/q7urjf9/social-media-discovery?action=share&creator=40783647&ctx=documentation)
