<?php

/**
 * Single source of truth for portfolio content.
 *
 * Everything the site renders comes from here by default. When a database is
 * configured and reachable, matching rows override these values (see
 * src/Content.php) so the admin panel stays useful — but the site renders
 * completely and correctly with no database at all.
 *
 * Content is drawn from the owner's CV and public GitHub profile. Do not add
 * claims that cannot be evidenced by either source.
 */

declare(strict_types=1);

return [

    /* ---------------------------------------------------------------- identity */

    'identity' => [
        'name'        => 'Afifa Sultana',
        'first_name'  => 'Afifa',
        'last_name'   => 'Sultana',
        'title'       => 'Software Engineer',
        'subtitle'    => 'CSE undergraduate at KUET',
        'tagline'     => 'I build backend systems and full-stack products.',
        'location'    => 'Khulna, Bangladesh',
        'email'       => 'afifasultana637@gmail.com',
        // Deliberately omitted. This file is published in a public repository and
        // the site is indexed, so a personal number here is harvestable by
        // scrapers. Recruiters reach out by email or LinkedIn; the number
        // belongs on the CV that is sent to a named recipient, not on the
        // open web. Add 'phone' => '…' back only for a private deployment.
        'availability' => 'Open to internships & junior roles',
        'available'   => true,
        'avatar'      => 'assets/images/profile1.jpg',
        'resume'      => 'download_cv.php',
        'github_user' => 'Afifa637',

        // Rotated by the hero type-effect.
        'roles' => [
            'Backend Engineer',
            'Full-Stack Developer',
            'Systems Builder',
            'Problem Solver',
        ],

        // Short, factual positioning statement shown under the hero title.
        'pitch' => 'Computer Science undergraduate at KUET building production-shaped software — '
            . 'Spring Boot services with JWT auth and role-based access, Laravel and PHP web apps, '
            . 'Flutter and Android clients, and a compiler written from scratch in C. '
            . 'I care about the parts users never see: schema design, migrations, auth boundaries, and clean deploys.',
    ],

    /* ----------------------------------------------------------------- socials */

    'socials' => [
        ['label' => 'GitHub',   'url' => 'https://github.com/Afifa637',                              'icon' => 'github'],
        ['label' => 'LinkedIn', 'url' => 'https://www.linkedin.com/in/afifa-sultana-346a13256/',     'icon' => 'linkedin'],
        ['label' => 'Email',    'url' => 'mailto:afifasultana637@gmail.com',                          'icon' => 'mail'],
    ],

    /* ------------------------------------------------------------------- about */

    'about' => [
        'lead' => 'I am a third-year Computer Science and Engineering student at Khulna University '
            . 'of Engineering & Technology, and I spend most of my time building things that have a backend.',

        'body' => [
            'My work has gravitated toward server-side engineering. I have built Spring Boot services '
            . 'with JWT-secured REST APIs, role-based dashboards, PostgreSQL persistence through JPA, '
            . 'and Liquibase migrations running in Docker — the kind of setup where getting the schema '
            . 'and the auth boundaries right matters more than the UI.',

            'I also like going lower. I wrote a working mini-compiler in C for a small language, '
            . 'built a multi-agent AI game in Godot to compare pathfinding and decision strategies '
            . 'against each other, and put together a solar tracker in C++. Those projects taught me '
            . 'more about how systems actually behave than any tutorial did.',

            'On the product side I ship full-stack: Laravel and PHP applications, a React ration-'
            . 'distribution system deployed on Vercel, Flutter and Android clients backed by Firebase. '
            . 'I am looking for internships or junior roles where I can work on real backend systems '
            . 'with people who will review my code honestly.',
        ],

        'facts' => [
            ['label' => 'Based in',   'value' => 'Khulna, Bangladesh'],
            ['label' => 'Studying',   'value' => 'B.Sc. CSE, KUET'],
            ['label' => 'Focus',      'value' => 'Backend · Full-stack'],
            ['label' => 'Languages',  'value' => 'Bangla, English, Hindi'],
        ],
    ],

    /* ------------------------------------------------------------------ skills */

    /**
     * Grouped by domain rather than scored by percentage — a self-assigned
     * "87% at PHP" communicates nothing verifiable to a reviewer. Every entry
     * below appears in a public repository or on the CV.
     */
    'skills' => [
        [
            'group' => 'Languages',
            'icon'  => 'code',
            'note'  => 'Used across coursework and shipped projects',
            'items' => ['Java', 'PHP', 'C++', 'C', 'Python', 'JavaScript', 'TypeScript', 'Dart', 'Swift', 'GDScript', 'SQL'],
        ],
        [
            'group' => 'Backend',
            'icon'  => 'server',
            'note'  => 'Where most of my project work lives',
            'items' => ['Spring Boot 3', 'Spring Security', 'Laravel', 'REST APIs', 'JWT Auth', 'JPA / Hibernate', 'Thymeleaf', 'Swagger / OpenAPI'],
        ],
        [
            'group' => 'Frontend',
            'icon'  => 'layout',
            'note'  => 'Server-rendered and SPA',
            'items' => ['HTML5', 'CSS3', 'React', 'Blade', 'Responsive Design', 'Accessibility'],
        ],
        [
            'group' => 'Data',
            'icon'  => 'database',
            'note'  => 'Schema design, migrations, queries',
            'items' => ['PostgreSQL', 'MySQL', 'Firebase Firestore', 'Liquibase', 'Schema Design'],
        ],
        [
            'group' => 'Mobile',
            'icon'  => 'smartphone',
            'note'  => 'Native and cross-platform',
            'items' => ['Flutter', 'Android (Java/Kotlin)', 'SwiftUI', 'Firebase Auth'],
        ],
        [
            'group' => 'Tooling',
            'icon'  => 'settings',
            'note'  => 'Day-to-day workflow',
            'items' => ['Git', 'GitHub', 'Docker', 'Docker Compose', 'Maven', 'Composer', 'Linux', 'Postman'],
        ],
    ],

    /* --------------------------------------------------------------- education */

    'education' => [
        [
            'degree'      => 'B.Sc. in Computer Science and Engineering',
            'institution' => 'Khulna University of Engineering & Technology',
            'location'    => 'Khulna, Bangladesh',
            'start'       => '2023',
            'end'         => 'Present',
            'current'     => true,
            'grade'       => 'CGPA 3.77 / 4.00',
            'grade_note'  => 'Computer Science & Engineering',
            'detail'      => 'Coursework across data structures, algorithms, database systems, compiler design, '
                . 'operating systems, software engineering, and artificial intelligence.',
        ],
        [
            'degree'      => 'Higher Secondary School Certificate',
            'institution' => 'Viqarunnisa Noon School and College',
            'location'    => 'Dhaka, Bangladesh',
            'start'       => '2019',
            'end'         => '2021',
            'current'     => false,
            'grade'       => 'GPA 5.00 / 5.00 (With Scholarship)',
            'grade_note'  => 'Science',
            'detail'      => 'Science group, with mathematics and physics as the primary focus.',
        ],
        [
            'degree'      => 'Secondary School Certificate',
            'institution' => 'Viqarunnisa Noon School and College',
            'location'    => 'Dhaka, Bangladesh',
            'start'       => '2014',
            'end'         => '2019',
            'current'     => false,
            'grade'       => 'GPA 5.00 / 5.00',
            'grade_note'  => 'Science',
            'detail'      => '',
        ],
    ],

    /* -------------------------------------------------------------- activities */

    'activities' => [
        [
            'role'   => 'Member',
            'org'    => 'SGIPC — Sub-Group of Inter-University Programming Contest',
            'detail' => 'KUET\'s competitive programming group. Regular practice contests and algorithmic problem solving.',
            'icon'   => 'terminal',
        ],
        [
            'role'   => 'Member',
            'org'    => 'HACK — Hardware Acceleration Club of KUET',
            'detail' => 'Hardware and embedded systems club, where the solar tracker and microcontroller work came from.',
            'icon'   => 'cpu',
        ],
        [
            'role'   => 'Participant',
            'org'    => 'BCF Hackathon 2026 — Preliminary Round',
            'detail' => 'Took part in the preliminary round of the Bangladesh Computer Federation hackathon.',
            'icon'   => 'award',
        ],
    ],

    /* ---------------------------------------------------------------- services */

    'services' => [
        [
            'title' => 'Backend & API Development',
            'icon'  => 'server',
            'body'  => 'REST APIs with authentication, role-based authorization, validation, and documented endpoints. '
                . 'Spring Boot or PHP, with the database designed rather than improvised.',
        ],
        [
            'title' => 'Full-Stack Web Applications',
            'icon'  => 'layers',
            'body'  => 'End-to-end builds — schema, server, and interface. Laravel, Spring Boot with Thymeleaf, '
                . 'React front-ends, or plain PHP where that is the right size for the job.',
        ],
        [
            'title' => 'Database Design & Migrations',
            'icon'  => 'database',
            'body'  => 'Relational schema design, indexing, and versioned migrations with Liquibase or SQL, '
                . 'so a schema change is a reviewable commit rather than a manual edit in production.',
        ],
        [
            'title' => 'Mobile Applications',
            'icon'  => 'smartphone',
            'body'  => 'Flutter and native Android clients with Firebase authentication and cloud data, '
                . 'built against the same API contracts as the web.',
        ],
    ],

    /* ---------------------------------------------------------- case studies */

    /**
     * Curated case studies.
     *
     * Every technical claim here is traceable to a public repository or to the
     * project descriptions in the portfolio database. `repo` links an entry to
     * live GitHub data (language, stars, last push), merged in at render time
     * by src/GitHub.php.
     *
     * Image filenames are case-exact: Linux hosting is case-sensitive, so
     * 'greengrocer.png' would 404 where 'GreenGrocer.png' resolves.
     */
    'projects' => [
        [
            'slug'     => 'timeless',
            'repo'     => 'TimeLess_watch_marketplace',
            'title'    => 'Timeless',
            'subtitle' => 'Luxury watch resale marketplace',
            'category' => 'backend',
            'featured' => true,
            'year'     => '2026',
            'image'    => '',
            'summary'  => 'A Spring Boot 3 marketplace for reselling luxury watches, serving browsable '
                . 'server-rendered pages to buyers and JWT-secured REST APIs to everything else.',
            'problem'  => 'A resale marketplace needs two front doors: pages a buyer can browse and a '
                . 'search engine can read, and an API a client application can call. Both have to enforce '
                . 'the same rules about who may list, edit, or approve a listing.',
            'role'     => 'Sole developer — schema, backend, security, templates',
            'stack'    => ['Java', 'Spring Boot 3', 'Spring Security', 'JWT', 'Thymeleaf', 'PostgreSQL', 'JPA', 'Liquibase', 'Docker'],
            'features' => [
                'Server-rendered Thymeleaf pages alongside a JWT-secured REST API over one domain model',
                'Role-based dashboards separating buyer, seller, and administrator capabilities',
                'PostgreSQL persistence through JPA, with Liquibase owning every schema change',
                'Docker-first setup so the database and application start from a single command',
            ],
            'challenges' => 'Running session-based page rendering and stateless JWT APIs under one Spring '
                . 'Security configuration. The answer was splitting the filter chain by request path, so '
                . 'browser pages keep their session while API routes stay stateless.',
            'outcome'  => 'A complete marketplace that comes up from one compose command — and the project '
                . 'that taught me the most about authorization design.',
            'learned'  => 'Multiple security filter chains, Liquibase changelog discipline, and why DTOs '
                . 'exist instead of returning entities straight out of a controller.',
            'architecture' => [
                ['layer' => 'Clients', 'tech' => 'Thymeleaf pages, REST clients', 'role' => 'Browsable pages for buyers; JSON for everything else'],
                ['layer' => 'Security filter chains', 'tech' => 'Spring Security, JWT, sessions', 'role' => 'Sessions for page routes, stateless JWT for /api routes'],
                ['layer' => 'Controllers', 'tech' => 'Spring MVC, REST', 'role' => 'Map requests onto DTOs rather than exposing entities'],
                ['layer' => 'Service layer', 'tech' => 'Spring services', 'role' => 'Listing, resale and approval rules'],
                ['layer' => 'Repositories', 'tech' => 'Spring Data JPA', 'role' => 'Persistence behind repository interfaces'],
                ['layer' => 'Database', 'tech' => 'PostgreSQL, Liquibase', 'role' => 'Every schema change is a reviewed changelog'],
            ],
            'demo_request' => 'POST /api/auth/login',
            'decisions' => 'Split Spring Security into two filter chains by request path, so server-rendered pages keep their session while /api routes stay stateless behind JWT.

Return DTOs from controllers instead of JPA entities, so the API contract does not leak persistence details.

Let Liquibase own every schema change, and run the whole stack from Docker Compose so the database and application start together.',
            'security' => 'JWT-secured REST API alongside session-based page rendering, under one Spring Security configuration. Role-based dashboards separate buyer, seller and administrator capabilities.',
            'goal'   => '',
            'future' => '',
        ],
        [
            'slug'     => 'amar-ration',
            'repo'     => 'Amar-Ration',
            'title'    => 'Amar Ration',
            'subtitle' => 'Ration distribution management system',
            'category' => 'fullstack',
            'featured' => true,
            'year'     => '2026',
            'demo'     => 'https://amar-ration.vercel.app',
            'image'    => '',
            'summary'  => 'A web system tracking ration beneficiaries, stock levels, and distribution '
                . 'records, so that who received what becomes a query instead of a paper trail.',
            'problem'  => 'Ration distribution is often recorded on paper, which makes double-collection '
                . 'hard to catch and stock reconciliation slow. The records need to be queryable, and the '
                . 'stock ledger has to stay consistent with what was actually handed out.',
            'role'     => 'Sole developer — data model, application, deployment',
            'stack'    => ['JavaScript', 'React', 'Node.js', 'REST APIs', 'Vercel'],
            'features' => [
                'Beneficiary registry with searchable records',
                'Stock tracking that decrements against each recorded distribution',
                'Per-beneficiary distribution history, which surfaces duplicate collection',
                'Reporting views over distribution and remaining stock',
            ],
            'challenges' => 'Keeping the stock ledger and the distribution log from drifting apart. Every '
                . 'handout has to move both, or the numbers stop meaning anything.',
            'outcome'  => 'Deployed and publicly reachable on Vercel.',
            'learned'  => 'That the hard part of an inventory system is not the interface — it is deciding '
                . 'what the single source of truth for a quantity is.',
            'architecture' => [
                ['layer' => 'Client', 'tech' => 'React', 'role' => 'Beneficiary search, distribution entry, reports'],
                ['layer' => 'REST API', 'tech' => 'Node.js', 'role' => 'Distribution and stock endpoints'],
                ['layer' => 'Ledger rules', 'tech' => 'JavaScript', 'role' => 'Every handout moves stock and the distribution log together'],
                ['layer' => 'Hosting', 'tech' => 'Vercel', 'role' => 'Publicly deployed'],
            ],
            'decisions' => 'Treat each handout as one operation that updates both the stock ledger and the distribution log, so the two can never drift apart.',
            'goal'   => '',
            'future' => '',
        ],
        [
            'slug'     => 'socialstory',
            'repo'     => 'SocialStory--A-Compiler-Project',
            'title'    => 'SocialStory',
            'subtitle' => 'A compiler for a small language, written in C',
            'category' => 'systems',
            'featured' => true,
            'year'     => '2026',
            'image'    => '',
            'summary'  => 'A mini-compiler for SocialScript, a small purpose-built language — lexer, '
                . 'parser, symbol table, and semantic analysis, implemented from scratch in C.',
            'problem'  => 'Compiler theory is straightforward on a whiteboard and considerably less so in '
                . 'practice. The goal was the full pipeline rather than a toy parser: source text in, '
                . 'meaningful diagnostics or output out.',
            'role'     => 'Developer — language design and compiler pipeline',
            'stack'    => ['C', 'Lex / Flex', 'Yacc / Bison', 'Compiler Design', 'Parsing'],
            'features' => [
                'Lexical analyser tokenising the SocialScript grammar',
                'Parser building a syntax tree from the token stream',
                'Symbol table handling scope and declaration checks',
                'Semantic analysis reporting located errors on invalid programs',
            ],
            'challenges' => 'Grammar ambiguity. Every conflict the parser generator reported turned out to '
                . 'be a place where the language design itself was unclear, not just the grammar file.',
            'outcome'  => 'A working compiler that accepts valid SocialScript programs and rejects invalid '
                . 'ones with error messages that point at the right line.',
            'learned'  => 'How languages are actually parsed, why grammar conflicts arise, and what a '
                . 'symbol table is really for.',
            'architecture' => [
                ['layer' => 'Source', 'tech' => 'SocialScript', 'role' => 'Program text in the custom language'],
                ['layer' => 'Lexer', 'tech' => 'Flex', 'role' => 'Tokenises the grammar'],
                ['layer' => 'Parser', 'tech' => 'Bison', 'role' => 'Builds the syntax tree'],
                ['layer' => 'Symbol table', 'tech' => 'C', 'role' => 'Scope and declaration checks'],
                ['layer' => 'Semantic analysis', 'tech' => 'C', 'role' => 'Rejects invalid programs with located errors'],
            ],
            'decisions' => 'Treat every parser-generator conflict as a question about the language design rather than a grammar-file bug to silence.',
            'goal'   => '',
            'future' => '',
        ],
        [
            'slug'     => 'prison-break',
            'repo'     => 'Prison_break',
            'title'    => 'Prison Break',
            'subtitle' => 'Multi-agent AI competition in Godot',
            'category' => 'ai',
            'featured' => true,
            'year'     => '2026',
            'image'    => '',
            'summary'  => 'A prison-escape game in Godot where several AI techniques compete at the same '
                . 'problem, so their behaviour can be compared directly inside one environment.',
            'problem'  => 'Comparing AI approaches from papers is awkward because each is demonstrated on '
                . 'its own benchmark. Putting several techniques into one dynamic environment, chasing the '
                . 'same objective, makes the differences visible rather than theoretical.',
            'role'     => 'Developer — environment, agent behaviours, simulation',
            'stack'    => ['GDScript', 'Godot Engine', 'Pathfinding', 'Multi-Agent Systems', 'Game AI'],
            'features' => [
                'A shared prison environment with dynamic obstacles and patrolling guards',
                'Multiple agent strategies solving the same escape objective',
                'Direct comparison of how each technique behaves under identical conditions',
                'Observable runs, so failure modes are watched rather than inferred from logs',
            ],
            'challenges' => 'Keeping the comparison fair. Any advantage accidentally baked into the map or '
                . 'the guard behaviour favours one strategy and invalidates the whole exercise.',
            'outcome'  => 'A playable environment where the strengths and failure modes of each approach '
                . 'are visible instead of described.',
            'learned'  => 'Pathfinding under changing conditions, agent state machines, and how easily a '
                . 'benchmark can be biased without anyone intending it.',
            'architecture' => [
                ['layer' => 'Environment', 'tech' => 'Godot Engine', 'role' => 'Shared prison map with dynamic obstacles and guards'],
                ['layer' => 'Agents', 'tech' => 'GDScript', 'role' => 'One agent per strategy'],
                ['layer' => 'Strategies', 'tech' => 'Pathfinding, decision logic', 'role' => 'Different techniques, same escape objective'],
                ['layer' => 'Observation', 'tech' => 'Godot', 'role' => 'Runs are watched, not only logged'],
            ],
            'decisions' => 'Keep the map and guard behaviour neutral, because any advantage baked into the environment invalidates the comparison between strategies.',
            'goal'   => '',
            'future' => '',
        ],
        [
            'slug'     => 'agroconnect',
            'repo'     => 'AgroConnect01',
            'title'    => 'AgroConnect',
            'subtitle' => 'Multi-vendor agricultural marketplace',
            'category' => 'fullstack',
            'featured' => true,
            'year'     => '2025',
            'image'    => 'assets/images/agroconnect1.jpg',
            'summary'  => 'A Laravel marketplace that cuts intermediaries out of the farming supply chain: '
                . 'farmers list produce, buyers bid on it, and both sides negotiate through a real-time '
                . 'interaction layer.',
            'problem'  => 'Traditional agricultural supply chains route small farmers through several '
                . 'intermediaries, each taking a margin, and the farmer rarely learns what the produce '
                . 'finally sold for. Direct listing with open bidding makes the price visible to both sides.',
            'role'     => 'Backend & system designer',
            'stack'    => ['PHP', 'Laravel', 'Blade', 'MySQL', 'JavaScript', 'Bootstrap', 'Eloquent ORM'],
            'features' => [
                'Multi-vendor listings, with farmers publishing produce and availability',
                'Bidding flow that lets buyers compete on price openly',
                'Real-time interaction layer for negotiation between the two parties',
                'Role-based access control separating farmer, buyer, and administrator',
                'Secure authentication with transparent, auditable pricing records',
            ],
            'challenges' => 'Modelling two user types with genuinely different capabilities — and a bidding '
                . 'flow where both act on the same listing — without duplicating the authentication layer '
                . 'or letting either role reach the other\'s actions.',
            'outcome'  => 'A working marketplace covering listing, bidding, negotiation, and fulfilment.',
            'learned'  => 'Laravel in depth — Eloquent relationships, middleware, policy-based authorization, '
                . 'and Blade layouts — plus how much of marketplace design is really access control.',
            'architecture' => [
                ['layer' => 'Browser', 'tech' => 'Blade, Bootstrap, JavaScript', 'role' => 'Listings, bids and negotiation views'],
                ['layer' => 'Routes & middleware', 'tech' => 'Laravel middleware, RBAC', 'role' => 'Separates farmer, buyer and administrator'],
                ['layer' => 'Controllers', 'tech' => 'Laravel', 'role' => 'Listing, bidding and negotiation flows'],
                ['layer' => 'Domain models', 'tech' => 'Eloquent ORM', 'role' => 'Users, listings and bids as related models'],
                ['layer' => 'Database', 'tech' => 'MySQL', 'role' => 'Relational store for the marketplace'],
            ],
            'demo_request' => 'POST /listings/{id}/bids',
            'decisions' => 'Model farmers and buyers as roles over one authentication layer, with Laravel middleware and policies deciding which actions each may take on a listing.

Keep pricing records auditable so both sides can see how a price was reached.',
            'security' => 'Role-based access control separating farmer, buyer and administrator, with secure authentication and policy-based authorization on listing actions.',
            'goal'   => '',
            'future' => '',
        ],
        [
            'slug'     => 'solartracker',
            'repo'     => 'SolarTracker',
            'title'    => 'SolarTracker',
            'subtitle' => 'IoT single-axis solar tracking system',
            'category' => 'systems',
            'featured' => true,
            'year'     => '2025',
            'image'    => '',
            'summary'  => 'An ESP32-based single-axis tracker that orients a solar panel toward the '
                . 'brightest light source, with live power telemetry streamed to a Blynk dashboard.',
            'problem'  => 'A fixed panel loses a substantial share of the energy available across a day. '
                . 'Tracking the sun recovers it — but only if the control loop settles instead of hunting, '
                . 'and only if the gain can actually be measured rather than assumed.',
            'role'     => 'IoT system developer — firmware, sensing, telemetry',
            'stack'    => ['C++', 'ESP32', 'Arduino', 'IoT', 'Blynk', 'INA219', 'Embedded Systems'],
            'features' => [
                'LDR sensor array detecting directional light intensity',
                'Servo actuation orienting the panel toward the strongest reading',
                'INA219 sensors reporting real-time voltage, current, and power',
                'Blynk integration for remote telemetry and load control',
            ],
            'challenges' => 'Sensor noise drove constant small corrections and the servo never settled. '
                . 'Thresholding and smoothing the readings — rather than reacting to every sample — turned '
                . 'an oscillating panel into a stable one.',
            'outcome'  => 'A tracker that follows the light smoothly, holds position, and reports measured '
                . 'power output rather than a claimed improvement.',
            'learned'  => 'That real sensors are noisy, control loops need hysteresis, and measuring the '
                . 'result is what separates a demo from a system.',
            'architecture' => [
                ['layer' => 'Sensing', 'tech' => 'LDR array, INA219', 'role' => 'Directional light; voltage, current and power'],
                ['layer' => 'Controller', 'tech' => 'ESP32, C++', 'role' => 'Control loop with thresholds and smoothing'],
                ['layer' => 'Actuation', 'tech' => 'Servo', 'role' => 'Single-axis panel orientation'],
                ['layer' => 'Telemetry', 'tech' => 'Blynk', 'role' => 'Remote monitoring and load control'],
            ],
            'decisions' => 'Threshold and smooth sensor readings instead of reacting to every sample, so the servo settles rather than hunting.

Measure real power with INA219 sensors rather than assuming a tracking gain.',
            'goal'   => '',
            'future' => '',
        ],
        [
            'slug'     => 'pulseverse',
            'repo'     => 'Social_media',
            'title'    => 'PulseVerse',
            'subtitle' => 'Full-featured Flutter social platform',
            'category' => 'mobile',
            'featured' => false,
            'year'     => '2025',
            'image'    => 'assets/images/pulseverse.jpg',
            'summary'  => 'A social media application in Flutter covering the full core loop — accounts, '
                . 'posts, likes, comments, following, and real-time chat — with dark and light themes.',
            'problem'  => 'A social feed is a state-management problem before it is a UI problem: the same '
                . 'post appears in several places at once, and every copy has to agree after any interaction.',
            'role'     => 'Developer — full application',
            'stack'    => ['Dart', 'Flutter', 'Firebase', 'Firebase Auth', 'Cloud Firestore', 'Material Design'],
            'features' => [
                'Sign-up and login with Firebase Authentication',
                'Post creation with likes and comments',
                'Follow relationships driving a personalised feed',
                'Real-time chat between users',
                'Dark and light themes across every screen',
            ],
            'challenges' => 'Keeping shared state consistent across widgets without rebuilding half the '
                . 'tree on every like, and making real-time chat feel instant over Firestore streams.',
            'outcome'  => 'A cross-platform client covering the features people actually expect from a '
                . 'social app, not just a feed mock-up.',
            'learned'  => 'Flutter state management, Firestore stream subscriptions, and theming a full '
                . 'application rather than a single screen.',
            'architecture' => [
                ['layer' => 'Client', 'tech' => 'Flutter, Dart', 'role' => 'Feed, profiles, chat; dark and light themes'],
                ['layer' => 'Authentication', 'tech' => 'Firebase Auth', 'role' => 'Sign-up and login'],
                ['layer' => 'Data', 'tech' => 'Cloud Firestore', 'role' => 'Posts, likes, comments and follows'],
                ['layer' => 'Realtime', 'tech' => 'Firestore streams', 'role' => 'Live chat between users'],
            ],
            'goal'   => '',
            'future' => '',
        ],
        [
            'slug'     => 'fireflycatcher',
            'repo'     => 'FireflyCatcher',
            'title'    => 'FireflyCatcher',
            'subtitle' => 'React Native mindfulness game',
            'category' => 'mobile',
            'featured' => false,
            'year'     => '2025',
            'image'    => 'assets/images/firefly-catcher.jpg',
            'summary'  => 'A mobile mindfulness game in React Native where players catch fireflies drifting '
                . 'across a night sky, each catch returning points, affirmations, and soothing animation.',
            'problem'  => 'Most casual games optimise for tension. The design goal here was the opposite: '
                . 'reward attention and calm, and keep the animation smooth enough that the experience '
                . 'never becomes the thing breaking the mood.',
            'role'     => 'Developer',
            'stack'    => ['TypeScript', 'React Native', 'Expo', 'Animation', 'Mobile UI'],
            'features' => [
                'Interactive play with yellow, blue, and red fireflies, each scoring differently',
                'Combo mechanics rewarding sustained attention',
                'Affirmation messages and ambient animation on each catch',
                'Typed game state, so score and combo logic are checked at compile time',
            ],
            'challenges' => 'Holding a steady frame rate on mid-range phones once many animated entities '
                . 'were on screen at once.',
            'outcome'  => 'A complete, playable game that stays smooth on ordinary hardware.',
            'learned'  => 'React Native animation performance, Expo tooling, and how much TypeScript catches '
                . 'in game state that plain JavaScript hides until runtime.',
        ],
        [
            'slug'     => 'heritage-explorer',
            'repo'     => 'Heritage-Database-Project',
            'title'    => 'Heritage Explorer',
            'subtitle' => 'Cultural heritage information system',
            'category' => 'fullstack',
            'featured' => false,
            'year'     => '2026',
            'image'    => 'assets/images/heritage-explorer.jpg',
            'summary'  => 'A PHP and MySQL system for digitally preserving cultural and historical heritage '
                . 'sites, with structured records, search and filtering, and an administrative panel.',
            'problem'  => 'Heritage records are inherently relational — sites, periods, regions, and '
                . 'artefacts all reference one another — and flattening them into a single table makes '
                . 'every interesting question unanswerable.',
            'role'     => 'Full-stack developer — schema and application',
            'stack'    => ['PHP', 'MySQL', 'Relational Design', 'JavaScript'],
            'features' => [
                'Normalised schema spanning sites, periods, and regions',
                'Search and filtering across several related dimensions',
                'Administrative panel for managing records, images, and descriptions',
                'Structured presentation aimed at long-term digital archiving',
            ],
            'challenges' => 'Choosing how far to normalise — far enough that queries stay expressive, not '
                . 'so far that every page turns into a six-table join.',
            'outcome'  => 'A working catalogue whose queries answer real questions about the collection.',
            'learned'  => 'Practical normalisation, indexing where it actually matters, and designing for '
                . 'data that has to outlive the application.',
            'architecture' => [
                ['layer' => 'Browser', 'tech' => 'HTML, JavaScript', 'role' => 'Search and filtering interface'],
                ['layer' => 'Application', 'tech' => 'PHP', 'role' => 'Catalogue and administrative panel'],
                ['layer' => 'Database', 'tech' => 'MySQL', 'role' => 'Normalised schema across sites, periods and regions'],
            ],
            'decisions' => 'Normalise far enough that queries stay expressive, but not so far that every page becomes a six-table join.',
            'goal'   => '',
            'future' => '',
        ],
        [
            'slug'     => 'student-management',
            'repo'     => 'Student_Management1',
            'title'    => 'Student Management',
            'subtitle' => 'Spring Boot service with RBAC',
            'category' => 'backend',
            'featured' => false,
            'year'     => '2026',
            'image'    => '',
            'summary'  => 'A Spring Boot service for student records with role-based access control, '
                . 'versioned migrations, a containerised setup, and generated API documentation.',
            'problem'  => 'Student records are a textbook CRUD problem, which makes them a good place to '
                . 'get the surrounding engineering right instead: authorization, migrations, containers, '
                . 'and documentation that stays in step with the code.',
            'role'     => 'Sole developer',
            'stack'    => ['Java', 'Spring Boot', 'Spring Security', 'PostgreSQL', 'Liquibase', 'Docker', 'Swagger'],
            'features' => [
                'Role-based access control enforced on every endpoint',
                'PostgreSQL with Liquibase migrations tracked in version control',
                'Swagger / OpenAPI documentation generated from the controllers',
                'Docker setup bringing the service and database up together',
            ],
            'challenges' => 'Getting the authority model right — the distinction between authenticating a '
                . 'user and deciding what that user may do to one specific record.',
            'outcome'  => 'A cleanly structured service that documents itself and deploys as a container.',
            'learned'  => 'Method-level security, migration discipline, and generated API documentation.',
            'architecture' => [
                ['layer' => 'API clients', 'tech' => 'REST, Swagger UI', 'role' => 'Endpoints documented from the controllers'],
                ['layer' => 'Security', 'tech' => 'Spring Security, RBAC', 'role' => 'Every endpoint checks both identity and role'],
                ['layer' => 'REST controllers', 'tech' => 'Spring Web', 'role' => 'Request mapping and validation'],
                ['layer' => 'Service layer', 'tech' => 'Spring services', 'role' => 'Student-record rules'],
                ['layer' => 'Repositories', 'tech' => 'Spring Data JPA', 'role' => 'Data access'],
                ['layer' => 'Database', 'tech' => 'PostgreSQL, Liquibase', 'role' => 'Migrations tracked in version control'],
            ],
            'demo_request' => 'GET /api/students/{id}',
            'decisions' => 'Enforce role-based access with Spring Security on every endpoint, using method-level security where a rule depends on the record.

Keep Liquibase migrations in version control and generate Swagger documentation from the controllers, so docs and schema cannot drift from the code.',
            'security' => 'Role-based access control on every endpoint. The distinction the project is built around: authenticating a user is not the same as deciding what that user may do to one specific record.',
            'goal'   => '',
            'future' => '',
        ],
        [
            'slug'     => 'greengrocer',
            'repo'     => 'GreenGrocer',
            'title'    => 'GreenGrocer',
            'subtitle' => 'Android grocery marketplace',
            'category' => 'mobile',
            'featured' => false,
            'year'     => '2026',
            'image'    => 'assets/images/green-grocer.jpg',
            'summary'  => 'A native Android grocery shopping application backed by Firebase, with a full '
                . 'customer journey on one side and an administrative dashboard on the other.',
            'problem'  => 'A shopping app is really two applications sharing a database: what the customer '
                . 'does and what the shop owner needs to see. Both touch the same stock and order records '
                . 'from opposite directions.',
            'role'     => 'Mobile developer',
            'stack'    => ['Java', 'Android', 'XML', 'Firebase', 'Firebase Auth', 'Realtime Database'],
            'features' => [
                'Registration, login, and password reset through Firebase Authentication',
                'Browsing by category — fruits, vegetables, dairy, proteins, grains — with search and filters',
                'Cart with quantity adjustment, order placement, and order tracking through to delivery',
                'Admin dashboard for product CRUD, order status management, and revenue tracking',
            ],
            'challenges' => 'Keeping the customer and admin views of the same order consistent as its '
                . 'status moved from pending through dispatched to received.',
            'outcome'  => 'A complete two-sided application rather than a catalogue demo.',
            'learned'  => 'Android lifecycle and listener patterns, Firebase data modelling, and designing '
                . 'one schema that serves two very different audiences.',
            'architecture' => [
                ['layer' => 'Android client', 'tech' => 'Android, Java, XML', 'role' => 'Customer journey and admin dashboard'],
                ['layer' => 'Authentication', 'tech' => 'Firebase Auth', 'role' => 'Registration, login, password reset'],
                ['layer' => 'Data', 'tech' => 'Firebase Realtime Database', 'role' => 'Products, carts, orders and revenue'],
            ],
            'goal'   => '',
            'future' => '',
        ],
        [
            'slug'     => 'firebase-notes',
            'repo'     => '2107087_MC_Assignment',
            'title'    => 'Firebase Notes',
            'subtitle' => 'iOS notes app with per-user access',
            'category' => 'mobile',
            'featured' => false,
            'year'     => '2026',
            'image'    => '',
            'summary'  => 'A Swift application using Firebase Authentication and Firestore, where every '
                . 'note is scoped to the user who created it.',
            'problem'  => 'Multi-user cloud data is an authorization problem before it is a storage '
                . 'problem: the database itself has to refuse cross-user reads, not merely the app.',
            'role'     => 'Developer',
            'stack'    => ['Swift', 'Firebase Auth', 'Cloud Firestore', 'iOS'],
            'features' => [
                'Firebase Authentication for sign-up and sign-in',
                'Firestore notes partitioned by user ID',
                'Full create, read, update, and delete on a user\'s own notes',
            ],
            'challenges' => 'Understanding that client-side filtering is not access control — the security '
                . 'rules have to enforce it server-side, or the data is simply public with extra steps.',
            'outcome'  => 'A working app where users genuinely cannot reach one another\'s notes.',
            'learned'  => 'Firestore security rules and Swift\'s asynchronous data flow.',
            'architecture' => [
                ['layer' => 'iOS client', 'tech' => 'Swift', 'role' => 'Notes interface'],
                ['layer' => 'Authentication', 'tech' => 'Firebase Auth', 'role' => 'Sign-up and sign-in'],
                ['layer' => 'Security rules', 'tech' => 'Firestore rules', 'role' => 'Server-side enforcement of per-user access'],
                ['layer' => 'Data', 'tech' => 'Cloud Firestore', 'role' => 'Notes partitioned by user ID'],
            ],
            'security' => 'Access is enforced by Firestore security rules on the server, not by filtering on the client — client-side filtering is not access control.',
            'goal'   => '',
            'future' => '',
        ],
        [
            'slug'     => 'coffee-shop',
            'repo'     => 'Coffee-Shop',
            'title'    => 'Coffee Shop',
            'subtitle' => 'Flutter ordering application',
            'category' => 'mobile',
            'featured' => false,
            'year'     => '2025',
            'image'    => 'assets/images/coffee-shop.jpg',
            'summary'  => 'A Flutter ordering app for a coffee shop, covering browsing, cart, and order '
                . 'management behind a deliberately uncluttered interface.',
            'problem'  => 'Ordering apps fail on friction. Every extra tap between opening the app and '
                . 'placing an order is a reason not to finish it.',
            'role'     => 'Mobile developer',
            'stack'    => ['Dart', 'Flutter', 'Firebase', 'Material Design'],
            'features' => [
                'Product browsing across the shop catalogue',
                'Cart with a live item-count badge',
                'Order placement and tracking',
            ],
            'challenges' => 'Keeping the cart badge and the cart contents in sync across screens without '
                . 'threading state through every widget by hand.',
            'outcome'  => 'A clean, working ordering flow from browse to placed order.',
            'learned'  => 'Flutter state management and building an interface where the shortest path is '
                . 'the obvious one.',
        ],
        [
            'slug'     => 'hangman',
            'repo'     => 'Hangman-Game',
            'title'    => 'Hangman',
            'subtitle' => 'C++ OOP showcase with persistence',
            'category' => 'systems',
            'featured' => false,
            'year'     => '2025',
            'image'    => 'assets/images/hang-man.jpg',
            'summary'  => 'A terminal Hangman game written as a deliberate tour of C++ language features — '
                . 'abstract classes, runtime polymorphism, operator overloading, templates, and STL '
                . 'containers — with a file-backed leaderboard.',
            'problem'  => 'Language features are easy to recite and harder to place. Building one program '
                . 'where each feature earns its position forces the distinction.',
            'role'     => 'Developer',
            'stack'    => ['C++', 'OOP', 'Templates', 'STL', 'File I/O', 'Operator Overloading'],
            'features' => [
                'Abstract base class with runtime polymorphism across game entities',
                'Operator overloading and friend functions for game state handling',
                'Template class and STL vector / pair for the word and score structures',
                'File handling backing a leaderboard that survives between sessions',
            ],
            'challenges' => 'Using each feature where it genuinely fits rather than forcing it in — the '
                . 'point was a coherent program, not a checklist.',
            'outcome'  => 'A complete game with menu, instructions, persistent leaderboard, and clean exit.',
            'learned'  => 'Where C++ abstractions actually pay for themselves, and how much design pressure '
                . 'file persistence puts on a data model.',
        ],
        [
            'slug'     => 'travel-agency',
            'repo'     => 'Travel-Agency-Management-System-2107087-',
            'title'    => 'Travel Agency Management',
            'subtitle' => 'C++ booking and billing system',
            'category' => 'systems',
            'featured' => false,
            'year'     => '2024',
            'image'    => 'assets/images/travel-agency.jpg',
            'summary'  => 'A console travel agency system handling customer registration, flight, hotel, '
                . 'and transport bookings, cost calculation, and receipt generation.',
            'problem'  => 'A booking system has to hold several kinds of reservation against one customer '
                . 'and still produce a single correct total — the arithmetic is trivial, the bookkeeping is not.',
            'role'     => 'Developer',
            'stack'    => ['C++', 'OOP', 'File I/O', 'Data Structures'],
            'features' => [
                'Customer registration with identity and contact records',
                'Flight, hotel, and transport bookings against one customer',
                'Cost breakdown across booking types with receipt generation',
                'Record updates after a booking is made',
            ],
            'challenges' => 'Modelling three booking types that share behaviour but differ in detail, '
                . 'without three near-identical code paths.',
            'outcome'  => 'A working system that registers, books, prices, and issues receipts.',
            'learned'  => 'Class hierarchies applied to a real domain, and why receipts are the honest test '
                . 'of whether the data model holds together.',
        ],
        [
            'slug'     => 'snake-game',
            'repo'     => 'SnakeGame-Pygame-prac-',
            'title'    => 'Snake',
            'subtitle' => 'Python game loop and collision handling',
            'category' => 'systems',
            'featured' => false,
            'year'     => '2025',
            'image'    => 'assets/images/snake-game.jpg',
            'summary'  => 'The classic Snake game in Python and Pygame — movement, growth, random spawning, '
                . 'and collision detection against both the boundary and the snake itself.',
            'problem'  => 'Snake looks trivial until self-collision: the body is a moving queue, and the '
                . 'head has to be tested against a shape that changes every frame.',
            'role'     => 'Developer',
            'stack'    => ['Python', 'Pygame', 'Game Loop', 'Collision Detection'],
            'features' => [
                'Smooth arrow-key movement on a fixed tick',
                'Random apple spawning with growth on each catch',
                'Collision detection against boundaries and the snake\'s own body',
                'Score tracking across a run',
            ],
            'challenges' => 'Representing the body so that growth and self-collision both stay cheap as '
                . 'the snake gets long.',
            'outcome'  => 'A complete, playable implementation of the full game loop.',
            'learned'  => 'Frame-driven game loops, and choosing a data structure for how it will be '
                . 'queried rather than how it is described.',
        ],
    ],

    /* ------------------------------------------------------------ project cats */

    'project_categories' => [
        'all'       => 'All',
        'backend'   => 'Backend',
        'fullstack' => 'Full-Stack',
        'mobile'    => 'Mobile',
        'systems'   => 'Systems',
        'ai'        => 'AI & Games',
        'frontend'  => 'Frontend',
    ],

    /* ----------------------------------------------------------------- contact */

    'contact' => [
        'heading' => 'Let\'s build something',
        'lead'    => 'I am open to internships, junior engineering roles, and freelance backend work. '
            . 'If you have a project or a position in mind, send a message — I reply to everything.',
        'channels' => [
            ['label' => 'Email',    'value' => 'afifasultana637@gmail.com', 'href' => 'mailto:afifasultana637@gmail.com', 'icon' => 'mail'],
            ['label' => 'GitHub',   'value' => 'Afifa637',                  'href' => 'https://github.com/Afifa637',      'icon' => 'github'],
            ['label' => 'LinkedIn', 'value' => 'Afifa Sultana',             'href' => 'https://www.linkedin.com/in/afifa-sultana-346a13256/', 'icon' => 'linkedin'],
            ['label' => 'Location', 'value' => 'Khulna, Bangladesh',        'href' => '',                                 'icon' => 'map-pin'],
        ],
        'purposes' => ['Internship', 'Engineering role', 'Collaboration', 'Project', 'Other'],
    ],

    /* ----------------------------------------------------------- principles */

    /**
     * "How I think". Each principle names the projects that evidence it, so the
     * claim is checkable rather than decorative.
     */
    'principles' => [
        [
            'title'    => 'Systems before screens',
            'body'     => 'The interface is the cheapest layer to change. The schema, the API contract and the '
                . 'authorization rules are not, so those get designed first and the screens follow them.',
            'evidence' => ['timeless', 'student-management', 'heritage-explorer'],
        ],
        [
            'title'    => 'Security is part of the architecture',
            'body'     => 'Access control decided at the edge of the system, not patched onto it afterwards. '
                . 'Filtering data in the client is not authorization — the server has to refuse.',
            'evidence' => ['timeless', 'firebase-notes', 'agroconnect'],
        ],
        [
            'title'    => 'Data models matter',
            'body'     => 'Most bugs I have chased were really questions about where a value lives. Decide the '
                . 'single source of truth for a quantity before writing the code that changes it.',
            'evidence' => ['amar-ration', 'heritage-explorer', 'greengrocer'],
        ],
        [
            'title'    => 'Ship, measure, improve',
            'body'     => 'A deployed system teaches more than a polished demo. Measure what it actually does, '
                . 'then improve the part the measurement points at.',
            'evidence' => ['amar-ration', 'solartracker', 'timeless'],
        ],
    ],

    /* ------------------------------------------------------------ blueprint */

    /** The life of a request, annotated with the tools used at each stage. */
    'blueprint' => [
        [
            'stage' => 'Request',
            'body'  => 'A client calls a documented endpoint with a JSON body.',
            'tech'  => ['REST APIs', 'JSON', 'Swagger / OpenAPI'],
        ],
        [
            'stage' => 'Authentication',
            'body'  => 'Identity is established, then the role decides what the caller may do.',
            'tech'  => ['JWT', 'Spring Security', 'RBAC', 'Firebase Auth', 'Laravel middleware'],
        ],
        [
            'stage' => 'Business logic',
            'body'  => 'Validated DTOs reach a service layer that owns the rules.',
            'tech'  => ['Spring services', 'Laravel controllers', 'DTOs', 'Validation'],
        ],
        [
            'stage' => 'Database',
            'body'  => 'A designed schema, changed only through versioned migrations.',
            'tech'  => ['PostgreSQL', 'MySQL', 'JPA / Hibernate', 'Eloquent ORM', 'Liquibase'],
        ],
        [
            'stage' => 'Response',
            'body'  => 'A deliberate status code and a payload shaped for the client.',
            'tech'  => ['HTTP status codes', 'JSON', 'Thymeleaf', 'Blade'],
        ],
    ],

    /* -------------------------------------------------------------- journey */

    /** Growth, not chronology: each stage names what it taught and where. */
    'journey' => [
        [
            'period'   => '2014 – 2021',
            'title'    => 'Foundations',
            'body'     => 'Science stream at Viqarunnisa Noon School and College — mathematics and physics, '
                . 'and the habit of working a problem until it gives.',
            'tech'     => ['Mathematics', 'Physics'],
            'projects' => [],
        ],
        [
            'period'   => '2023 – 2025',
            'title'    => 'Programming in C and C++',
            'body'     => 'Started CSE at KUET. Learned to think in data structures and objects, and to make '
                . 'a program hold its state correctly from start to finish.',
            'tech'     => ['C', 'C++', 'OOP', 'File I/O'],
            'projects' => ['travel-agency', 'hangman'],
        ],
        [
            'period'   => '2025',
            'title'    => 'Data and the web',
            'body'     => 'Relational design, normalisation, and server-side PHP — then Laravel, where roles '
                . 'and authorization first became the hard part.',
            'tech'     => ['PHP', 'MySQL', 'Laravel', 'Eloquent ORM'],
            'projects' => ['heritage-explorer', 'agroconnect'],
        ],
        [
            'period'   => '2025',
            'title'    => 'Mobile clients',
            'body'     => 'Flutter, React Native and native Android against Firebase. State management, '
                . 'real-time streams, and two applications sharing one database.',
            'tech'     => ['Flutter', 'React Native', 'Android', 'Firebase'],
            'projects' => ['pulseverse', 'fireflycatcher', 'coffee-shop', 'greengrocer'],
        ],
        [
            'period'   => '2025',
            'title'    => 'Hardware and control',
            'body'     => 'An ESP32 solar tracker taught that real sensors are noisy and that a control loop '
                . 'needs hysteresis — and that measuring beats assuming.',
            'tech'     => ['ESP32', 'C++', 'Sensors', 'Blynk'],
            'projects' => ['solartracker'],
        ],
        [
            'period'   => '2026',
            'title'    => 'Backend engineering',
            'body'     => 'Spring Boot services with JWT, role-based access, JPA, Liquibase migrations and '
                . 'Docker — the work I want to keep doing.',
            'tech'     => ['Java', 'Spring Boot', 'Spring Security', 'PostgreSQL', 'Docker'],
            'projects' => ['student-management', 'timeless'],
        ],
        [
            'period'   => '2026',
            'title'    => 'Systems and AI',
            'body'     => 'A compiler written from scratch, and a multi-agent AI environment built to compare '
                . 'strategies fairly.',
            'tech'     => ['C', 'Flex', 'Bison', 'GDScript', 'Game AI'],
            'projects' => ['socialstory', 'prison-break'],
        ],
        [
            'period'   => '2026',
            'title'    => 'Shipping',
            'body'     => 'Deploying something people can actually use, and keeping its data honest.',
            'tech'     => ['React', 'Node.js', 'Vercel'],
            'projects' => ['amar-ration'],
        ],
    ],

    /* ----------------------------------------------------------- core graph */

    /**
     * Nodes for the hero System Core. Project counts and connections are
     * computed from project stacks at render time, never typed in by hand.
     * `match` lists the stack names a node stands for.
     */
    'core_nodes' => [
        ['id' => 'java',       'label' => 'Java',        'group' => 'backend',  'match' => ['Java']],
        ['id' => 'spring',     'label' => 'Spring Boot', 'group' => 'backend',  'match' => ['Spring Boot', 'Spring Boot 3', 'Spring Security']],
        ['id' => 'php',        'label' => 'PHP',         'group' => 'backend',  'match' => ['PHP']],
        ['id' => 'laravel',    'label' => 'Laravel',     'group' => 'backend',  'match' => ['Laravel', 'Blade', 'Eloquent ORM']],
        ['id' => 'rest',       'label' => 'REST APIs',   'group' => 'backend',  'match' => ['REST APIs', 'JWT', 'Swagger']],
        ['id' => 'postgres',   'label' => 'PostgreSQL',  'group' => 'data',     'match' => ['PostgreSQL']],
        ['id' => 'mysql',      'label' => 'MySQL',       'group' => 'data',     'match' => ['MySQL']],
        ['id' => 'flutter',    'label' => 'Flutter',     'group' => 'mobile',   'match' => ['Flutter', 'Dart']],
        ['id' => 'android',    'label' => 'Android',     'group' => 'mobile',   'match' => ['Android']],
        ['id' => 'react',      'label' => 'React',       'group' => 'frontend', 'match' => ['React', 'React Native']],
        ['id' => 'cpp',        'label' => 'C / C++',     'group' => 'systems',  'match' => ['C', 'C++']],
        ['id' => 'ai',         'label' => 'AI',          'group' => 'systems',  'match' => ['Game AI', 'Multi-Agent Systems', 'Pathfinding']],
        ['id' => 'docker',     'label' => 'Docker',      'group' => 'tooling',  'match' => ['Docker']],
        ['id' => 'git',        'label' => 'Git',         'group' => 'tooling',  'match' => [], 'all_repos' => true],
    ],

    /* --------------------------------------------------------------- status */

    'status' => [
        'focus'    => 'Backend engineering',
        'mode'     => 'Building',
        'timezone' => 'Asia/Dhaka',
        'version'  => '2.0',
    ],

    /* --------------------------------------------------------------------- seo */

    'seo' => [
        'title'       => 'Afifa Sultana — Software Engineer',
        'description' => 'Software engineer and CSE undergraduate at KUET. Spring Boot and PHP backends, '
            . 'full-stack web applications, Flutter and Android clients, and a compiler written in C.',
        'keywords'    => 'Afifa Sultana, software engineer, backend developer, Spring Boot, PHP, Laravel, '
            . 'Flutter, KUET, Bangladesh, full-stack developer',
        'image'       => 'assets/images/og-cover.png',
        'locale'      => 'en_US',
        'twitter'     => '',
    ],
];
