<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).













**************INSTRUCTION DE TESTE MISE EN PLACE PAR ELVIS POUR PERMETTRE DE TESTER L'API SIMPLEMENT VIA POSTMAN****************
 TEST 1 : INSCRIPTION
Objectif : Créer un nouveau compte utilisateur
Configuration : POST + Body JSON

text
POST http://localhost:8000/api/auth/register
Headers: Content-Type: application/json
Body (raw JSON):
json
{
    "name": "Test User",
    "email": "test@email.com",
    "password": "password123",
    "password_confirmation": "password123"
}
✅ Résultat attendu : Status 201 avec token

🔐 TEST 2 : CONNEXION
Objectif : Se connecter avec l'utilisateur créé
Configuration : POST + Body JSON

text
POST http://localhost:8000/api/auth/login
Headers: Content-Type: application/json
Body (raw JSON):
json
{
    "email": "test@email.com",
    "password": "password123"
}
✅ Résultat attendu : Status 200 avec token
➡️ COPIEZ CE TOKEN pour les tests suivants

🔐 TEST 3 : VOIR MON PROFIL
Objectif : Vérifier que le token fonctionne
Configuration : GET + Token Bearer

text
GET http://localhost:8000/api/auth/me
Authorization: Bearer {votre_token}
✅ Résultat attendu : Status 200 avec vos infos

🏢 TEST 4 : CRÉER UNE ENTREPRISE
Objectif : Ajouter une entreprise dans la base
Configuration : POST + JSON + Token

text
POST http://localhost:8000/api/companies
Authorization: Bearer {votre_token}
Headers: Content-Type: application/json
Body (raw JSON):
json
{
    "name": "Ma Société",
    "email": "contact@masociete.fr",
    "phone": "0123456789",
    "address": "123 Rue Principale",
    "city": "Paris",
    "country": "France",
    "siret": "12345678901234"
}
✅ Résultat attendu : Status 201
➡️ NOTEZ L'ID (ex: 1)

🏢 TEST 5 : LISTER LES ENTREPRISES
Objectif : Voir toutes les entreprises
Configuration : GET + Token

text
GET http://localhost:8000/api/companies
Authorization: Bearer {votre_token}
✅ Résultat attendu : Status 200, liste des entreprises

📊 TEST 6 : CRÉER UNE CATÉGORIE
Objectif : Ajouter une catégorie (dépense/revenu)
Configuration : POST + JSON + Token

text
POST http://localhost:8000/api/categories
Authorization: Bearer {votre_token}
Body (raw JSON):
json
{
    "name": "Fournisseurs",
    "description": "Dépenses fournisseurs",
    "color": "#FF5733",
    "is_active": true
}
✅ Résultat attendu : Status 201
➡️ NOTEZ L'ID

📄 TEST 7 : CRÉER UNE FACTURE
Objectif : Créer une facture pour une entreprise
Configuration : POST + JSON + Token

text
POST http://localhost:8000/api/invoices
Authorization: Bearer {votre_token}
Body (raw JSON):
json
{
    "invoice_number": "FACT-001",
    "company_id": 1,
    "user_id": 1,
    "amount": 1500.00,
    "issue_date": "2024-03-01",
    "due_date": "2024-03-31",
    "status": "sent",
    "notes": "Facture de test"
}
✅ Résultat attendu : Status 201
➡️ NOTEZ L'ID

💰 TEST 8 : CRÉER UNE TRANSACTION (REVENU)
Objectif : Enregistrer un paiement reçu
Configuration : POST + JSON + Token

text
POST http://localhost:8000/api/transactions
Authorization: Bearer {votre_token}
Body (raw JSON):
json
{
    "reference": "TR-001",
    "amount": 1500.00,
    "type": "income",
    "description": "Paiement reçu",
    "transaction_date": "2024-03-05",
    "category_id": 1,
    "company_id": 1,
    "invoice_id": 1,
    "payment_method": "transfer"
}
✅ Résultat attendu : Status 201

💰 TEST 9 : CRÉER UNE TRANSACTION (DÉPENSE)
Objectif : Enregistrer une dépense
Configuration : POST + JSON + Token

text
POST http://localhost:8000/api/transactions
Authorization: Bearer {votre_token}
Body (raw JSON):
json
{
    "reference": "TR-002",
    "amount": 300.00,
    "type": "expense",
    "description": "Achat fournitures",
    "transaction_date": "2024-03-06",
    "category_id": 1,
    "company_id": 1,
    "payment_method": "card"
}
✅ Résultat attendu : Status 201

💰 TEST 10 : LISTER LES TRANSACTIONS
Objectif : Voir toutes les transactions
Configuration : GET + Token

text
GET http://localhost:8000/api/transactions
Authorization: Bearer {votre_token}
✅ Résultat attendu : Status 200, liste des transactions

💰 TEST 11 : STATISTIQUES DES TRANSACTIONS
Objectif : Voir le total des revenus/dépenses
Configuration : GET + Token

text
GET http://localhost:8000/api/transactions/stats/summary
Authorization: Bearer {votre_token}
✅ Résultat attendu : Status 200 avec totaux

💸 TEST 12 : CRÉER UNE DETTE
Objectif : Enregistrer une dette à payer
Configuration : POST + JSON + Token

text
POST http://localhost:8000/api/debts
Authorization: Bearer {votre_token}
Body (raw JSON):
json
{
    "title": "Facture internet",
    "amount": 500.00,
    "remaining_amount": 500.00,
    "due_date": "2024-04-01",
    "company_id": 1,
    "user_id": 1,
    "status": "pending",
    "description": "Abonnement internet"
}
✅ Résultat attendu : Status 201
➡️ NOTEZ L'ID

💸 TEST 13 : LISTER LES DETTES
Objectif : Voir toutes les dettes
Configuration : GET + Token

text
GET http://localhost:8000/api/debts
Authorization: Bearer {votre_token}
✅ Résultat attendu : Status 200, liste des dettes

💸 TEST 14 : AJOUTER UN PAIEMENT SUR UNE DETTE
Objectif : Payer une partie d'une dette
Configuration : POST + JSON + Token

text
POST http://localhost:8000/api/debts/1/payments
Authorization: Bearer {votre_token}
Body (raw JSON):
json
{
    "amount": 200.00
}
✅ Résultat attendu : Status 200

📈 TEST 15 : TABLEAU DE BORD
Objectif : Voir les statistiques globales
Configuration : GET + Token

text
GET http://localhost:8000/api/dashboard
Authorization: Bearer {votre_token}
✅ Résultat attendu : Status 200 avec résumé

⚠️ TEST 16 : VOIR LES ALERTES
Objectif : Voir les notifications
Configuration : GET + Token

text
GET http://localhost:8000/api/alerts
Authorization: Bearer {votre_token}
✅ Résultat attendu : Status 200, liste des alertes

⚠️ TEST 17 : MARQUER UNE ALERTE COMME LUE
Objectif : Indiquer qu'on a vu l'alerte
Configuration : PATCH + Token

text
PATCH http://localhost:8000/api/alerts/1/read
Authorization: Bearer {votre_token}
✅ Résultat attendu : Status 200

📊 TEST 18 : RAPPORT MENSUEL
Objectif : Générer un rapport du mois
Configuration : GET + Token

text
GET http://localhost:8000/api/reports/monthly
Authorization: Bearer {votre_token}
✅ Résultat attendu : Status 200 avec données

💡 TEST 19 : CONSEILS DE DÉPENSES
Objectif : Recevoir des recommandations
Configuration : GET + Token

text
GET http://localhost:8000/api/insights/spending-tips
Authorization: Bearer {votre_token}
✅ Résultat attendu : Status 200 avec conseils

🗑️ TEST 20 : SUPPRIMER UNE TRANSACTION
Objectif : Effacer une transaction
Configuration : DELETE + Token

text
DELETE http://localhost:8000/api/transactions/1
Authorization: Bearer {votre_token}
✅ Résultat attendu : Status 200

🚪 TEST 21 : DÉCONNEXION
Objectif : Invalider le token
Configuration : POST + Token

text
POST http://localhost:8000/api/auth/logout
Authorization: Bearer {votre_token}
✅ Résultat attendu : Status 200

📝 RÉSUMÉ DES TESTS
#	Test	Méthode	URL
1	Inscription	POST	/api/auth/register
2	Connexion	POST	/api/auth/login
3	Mon profil	GET	/api/auth/me
4	Créer entreprise	POST	/api/companies
5	Lister entreprises	GET	/api/companies
6	Créer catégorie	POST	/api/categories
7	Créer facture	POST	/api/invoices
8	Transaction (revenu)	POST	/api/transactions
9	Transaction (dépense)	POST	/api/transactions
10	Lister transactions	GET	/api/transactions
11	Stats transactions	GET	/api/transactions/stats/summary
12	Créer dette	POST	/api/debts
13	Lister dettes	GET	/api/debts
14	Payer dette	POST	/api/debts/1/payments
15	Dashboard	GET	/api/dashboard
16	Voir alertes	GET	/api/alerts
17	Lire alerte	PATCH	/api/alerts/1/read
18	Rapport mensuel	GET	/api/reports/monthly
19	Conseils dépenses	GET	/api/insights/spending-tips
20	Supprimer transaction	DELETE	/api/transactions/1
21	Déconnexion	POST	/api/auth/logout
✅ À SAVOIR
Token : Après le test 2, copiez le token et mettez-le dans l'onglet "Authorization" → "Bearer Token"

IDs : Notez les IDs retournés (1, 2, etc.) pour les utiliser dans les URLs suivantes

Headers : Pour POST et PUT, toujours mettre Content-Type: application/json

Bonne découverte de votre API ! 🚀



