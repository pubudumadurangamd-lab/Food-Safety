# Food Safety & Premises Grading Management System (FSPGMS)
## Sri Lanka Health Department

### Overview
A complete web-based Food Safety Inspection and Public Complaint Management System developed for Sri Lanka's Ministry of Health. The system enables Public Health Inspectors (PHI) to conduct food premises inspections, grade establishments according to H800 criteria, manage worker medical certificates, and allow public users to search grades and submit complaints.

### System Statistics
- **Total Files:** 42
- **Total Lines of Code:** 8,357
- **Technology Stack:** PHP 8, MySQL, Bootstrap 5, JavaScript, Chart.js
- **Architecture:** MVC (Model-View-Controller)

### Features Implemented

#### 1. Authentication System
- Secure login with Argon2id password hashing
- Role-based access control (5 user roles)
- Password reset via email
- CSRF protection
- Rate limiting on login attempts
- Session management with regeneration

#### 2. Food Premises Management
- Register premises with GPS coordinates
- Photo upload with validation
- Automatic grade calculation based on H800 criteria
- QR code generation for each premises
- Public search with filters (district, grade)

#### 3. Inspection System (H800 Based)
- 9 inspection criteria with weighted scoring
- Automatic grade calculation (A/B/C/D)
- Photo documentation
- Inspection history tracking

#### 4. Public Complaint System
- Anonymous and registered user complaints
- Photo evidence upload
- Status tracking (Pending/Under Investigation/Resolved/Rejected)

#### 5. Medical Certificate Management
- Worker registration with NIC
- Medical certificate tracking
- Expiry alerts (30-day warning)
- PDF certificate download
- Auto-expiry detection

#### 6. Dashboard & Analytics
- Admin dashboard with comprehensive statistics
- Inspector dashboard with performance metrics
- Chart.js integration for data visualization

#### 7. Security Features
- SQL injection prevention (PDO prepared statements)
- XSS protection (output encoding)
- CSRF token validation
- Secure file upload validation
- Activity logging
- IP-based rate limiting

#### 8. UI/UX Features
- Responsive Bootstrap 5 design
- Green and white government theme
- Dark mode support
- Mobile-friendly sidebar navigation

### User Roles
1. **Super Admin** - Full system access
2. **PHI / Food Inspector** - Inspections, premises management
3. **Business Owner** - View own premises
4. **Worker** - View medical certificates
5. **Public User** - Search premises, submit complaints

### Installation
See INSTALL.md for detailed installation instructions.

### Default Credentials
| Role | Email | Password |
|------|-------|----------|
| Super Admin | admin@foodsafety.gov.lk | password |
| Inspector | phi.colombo@foodsafety.gov.lk | password |
| Business Owner | sunil@sunilrestaurant.lk | password |
| Worker | ravi.kumar@email.lk | password |
| Public User | kasun@email.lk | password |

**IMPORTANT: Change all default passwords after first login!**

### Version
1.0.0 - Released 2026

### Developed For
Ministry of Health, Democratic Socialist Republic of Sri Lanka

### License
Proprietary - Government of Sri Lanka
