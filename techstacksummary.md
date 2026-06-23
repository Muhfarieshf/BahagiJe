BahagiJe (BahagiJe) is based on a carefully curated technology stack focused on achieving zero deployment cost, development efficiency, and technical reliability for a single developer operating in an academic context over a limited timeframe. This architecture combines robust software standards with lightweight open-source libraries and cloud services to offset financial burdens without compromising on data integrity or user experience.

The back end is built with the CakePHP 5.x framework to manage all core business logic, ORM database interactions, routing, and scaffolding authentication. This Model-View-Controller (MVC) framework was selected for its strict built-in conventions, rapid development capabilities, and native support for secure parameterized queries that mitigate common web vulnerabilities.

The user interface is styled entirely using Tailwind CSS. Transitioning away from traditional component libraries, Tailwind's utility-first approach maximizes the speed of constructing custom, responsive, mobile-first interfaces tailored for smartphone viewports without suffering from CSS bloat or framework lock-in. To complement this, the MobileDetect library is used to detect the visitor's device type at the server level, enabling the application to serve the appropriate layout and routing behaviour for mobile versus desktop users.

Data persistence is handled by a MySQL relational database management system, chosen for its strict data typing, seamless compatibility with the CakePHP ORM, and availability on free-tier hosting platforms like InfinityFree. To manage heavy media assets such as payment proofs and physical receipt images, the application utilizes the Cloudinary API. This acts as a dedicated cloud storage layer, keeping the internal database lightweight by only storing external URL references rather than binary file blobs.

Session security and identity management are powered by Google OAuth 2.0 and UUID generation. Google OAuth 2.0 eliminates the need for complex local password hashing and offers verified identity tracking for registered users. Session sharing relies on randomly generated UUID identifiers to prevent unauthorized ID guessing, alongside the endroid/qr-code PHP library, which dynamically renders these secure URLs into scannable QR codes for frictionless, real-time group onboarding.

The platform's advanced spatial features—specifically the Road Trip preset—are driven by a stack of open-source geospatial tools. Leaflet.js provides the interactive map interface, utilizing map tiles from OpenStreetMap. Waypoint coordinate resolution is handled by the Nominatim API for geocoding, while the Leaflet Routing Machine dynamically draws chronological driving routes between user-defined stops.

The local development environment is powered by Laragon, a lightweight PHP/MySQL setup that supports Composer, virtual hosts, and rapid prototyping. To ensure the application is accessible and secure during live demonstrations without incurring paid infrastructure costs, Cloudflare Tunnels are used to safely expose the local Laragon environment, while InfinityFree serves as the public deployment platform for final operational delivery.

Technology	Category	Purpose
CakePHP 5.x	Backend Framework	MVC architecture, ORM, routing, server-side logic
MySQL	Database	Relational data storage and integrity constraints
Tailwind CSS	Frontend	Responsive, utility-first UI styling
MobileDetect	Frontend / Utility	Server-side mobile/desktop device detection for adaptive layout routing
Cloudinary API	File Storage	Receipt and payment proof image hosting
Google OAuth 2.0	Authentication	Secure third-party user identity management
UUID (v4)	Security	Cryptographically secure session URL generation
endroid/qr-code	Frontend / Utility	Dynamic QR code generation for session joining
Leaflet.js & OpenStreetMap	Frontend / GIS	Interactive map rendering and tile serving
Nominatim API	External Service	Geocoding text searches into latitude/longitude
Leaflet Routing Machine	Frontend / GIS	Calculating and drawing driving routes
Laragon	Local Development	Lightweight PHP/MySQL environment
Cloudflare Tunnels	Development Tool	Secure public tunneling for live local demonstrations
InfinityFree	Hosting	Free-tier public deployment platform
