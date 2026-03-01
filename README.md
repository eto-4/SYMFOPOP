# SymfoPop — Mercat de Segona Mà

Aplicació web de mercat de segona mà desenvolupada amb **Symfony 7.4**, **Doctrine ORM**, **Twig** i **Bootstrap 5**.

Els usuaris poden registrar-se, publicar productes, editar-los i esborrar-los. El llistat públic és accessible per a tothom sense necessitat d'autenticació.

---

## Tecnologies utilitzades

- **PHP 8.2+**
- **Symfony 7.4**
- **Doctrine ORM** — gestió de base de dades
- **Twig** — motor de plantilles
- **Bootstrap 5.3** — layout i components UI
- **CSS propi** (`public/styles/css/app.css`) — disseny visual
- **MySQL / MariaDB**
- **DoctrineFixturesBundle + Faker** — dades de prova

---

## Requisits previs

- PHP 8.2 o superior
- Composer
- Symfony CLI (`symfony`)
- MySQL o MariaDB
- Node.js (opcional, per a Asset Mapper)

---

## Instal·lació

### 1. Clonar el repositori

```bash
git clone https://github.com/usuari/symfopop.git
cd symfopop
```

### 2. Instal·lar dependències PHP

```bash
composer install
```

### 3. Configurar les variables d'entorn

Copia el fitxer d'exemple i edita'l amb les teves credencials:

```bash
cp .env.example .env
```

Edita el fitxer `.env` i configura la connexió a la base de dades:

```
DATABASE_URL="mysql://usuari:contrasenya@127.0.0.1:3306/symfopop?serverVersion=8.0"
```

### 4. Crear la base de dades

```bash
php bin/console doctrine:database:create
```

### 5. Executar les migracions

```bash
php bin/console doctrine:migrations:migrate
```

### 6. Carregar les dades de prova (opcional)

Carrega 5 usuaris i 20 productes generats amb Faker:

```bash
php bin/console doctrine:fixtures:load
```

> **Nota:** Tots els usuaris de prova tenen la contrasenya `password`.

### 7. Iniciar el servidor de desenvolupament

```bash
symfony serve
```

L'aplicació estarà disponible a `https://localhost:8000`.

---

## Estructura del projecte

```
symfopop/
├── config/
│   └── packages/
│       ├── security.yaml       # Configuració d'autenticació
│       └── doctrine.yaml       # Configuració de Doctrine ORM
├── src/
│   ├── Controller/
│   │   ├── HomeController.php          # Pàgina principal
│   │   ├── ProductController.php       # CRUD de productes
│   │   ├── RegistrationController.php  # Registre d'usuaris
│   │   └── SecurityController.php      # Login / Logout
│   ├── Entity/
│   │   ├── Product.php     # Entitat producte
│   │   └── User.php        # Entitat usuari
│   ├── Form/
│   │   ├── ProductType.php           # Formulari de producte
│   │   └── RegistrationFormType.php  # Formulari de registre
│   ├── Repository/
│   │   ├── ProductRepository.php
│   │   └── UserRepository.php
│   └── DataFixtures/
│       └── AppFixtures.php   # Dades de prova
├── templates/
│   ├── base.html.twig
│   ├── layout/
│   │   ├── header.html.twig
│   │   └── footer.html.twig
│   ├── product/
│   │   ├── index.html.twig   # Llistat de productes (reutilitzable)
│   │   ├── show.html.twig    # Detall d'un producte
│   │   ├── new.html.twig     # Formulari de creació
│   │   └── edit.html.twig    # Formulari d'edició
│   ├── security/
│   │   └── login.html.twig
│   └── registration/
│       └── register.html.twig
└── public/
    └── styles/css/
        └── app.css           # Estils visuals personalitzats
```

---

## Funcionalitats

| Funcionalitat | Descripció |
|---|---|
| 👤 Registre | Creació de compte amb nom, email i contrasenya |
| 🔐 Login / Logout | Autenticació amb email i "Recorda'm" |
| 🛒 Llistat públic | Tots els productes visibles sense autenticació |
| 👁️ Detall producte | Pàgina amb tota la informació del producte |
| ➕ Crear producte | Formulari protegit per a usuaris autenticats |
| ✏️ Editar producte | Només el propietari pot editar el seu producte |
| 🗑️ Esborrar producte | Només el propietari pot esborrar, amb protecció CSRF |
| 📦 Els meus productes | Llistat personal amb botons d'acció |
| 🔒 Validació de permisos | Accés denegat si no ets el propietari |
| 💬 Missatges flash | Confirmació visual de cada acció |

---

## Seguretat

- Contrasenyes hashejades amb `bcrypt` via `UserPasswordHasher`
- Protecció CSRF en tots els formularis de modificació i esborrat
- Rutes protegides amb `#[IsGranted('ROLE_USER')]`
- Validació de propietat al controlador abans de permetre editar o esborrar
- Escapament automàtic de variables a Twig (protecció XSS)

---

## Comptes de prova

Després de carregar les fixtures, pots entrar amb qualsevol dels usuaris generats. Consulta els emails a la base de dades o usa el Symfony Profiler. La contrasenya de tots és:

```
password
```

---

## Comandes útils

```bash
# Netejar la caché
php bin/console cache:clear

# Veure totes les rutes registrades
php bin/console debug:router

# Crear una nova migració després de canviar entitats
php bin/console make:migration
php bin/console doctrine:migrations:migrate

# Generar i Recarregar fixtures (esborra i torna a carregar)
php bin/console doctrine:fixtures:load --no-interaction
```

---

## Llicència

Projecte acadèmic — DAW / Symfony 7.4
