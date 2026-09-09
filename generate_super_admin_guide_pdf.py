import os
import sys
from reportlab.lib.pagesizes import letter
from reportlab.lib import colors
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, PageBreak, KeepTogether, HRFlowable
)
from reportlab.pdfgen import canvas

class NumberedCanvas(canvas.Canvas):
    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        self._saved_page_states = []

    def showPage(self):
        self._saved_page_states.append(dict(self.__dict__))
        self._startPage()

    def save(self):
        num_pages = len(self._saved_page_states)
        for state in self._saved_page_states:
            self.__dict__.update(state)
            self.draw_page_decorations(num_pages)
            super().showPage()
        super().save()

    def draw_page_decorations(self, page_count):
        self.saveState()
        self.setFont("Helvetica", 8)
        self.setFillColor(colors.HexColor("#627094"))
        
        # Header (pages > 1)
        if self._pageNumber > 1:
            self.drawString(40, 760, "MEDISTORE SaaS — Super Admin Complete Architecture Guide")
            self.setStrokeColor(colors.HexColor("#E2E8F0"))
            self.setLineWidth(0.75)
            self.line(40, 752, 572, 752)
        
        # Footer
        self.setStrokeColor(colors.HexColor("#E2E8F0"))
        self.setLineWidth(0.75)
        self.line(40, 45, 572, 45)
        
        self.drawString(40, 32, "Confidential — Internal System Documentation")
        page_text = f"Page {self._pageNumber} of {page_count}"
        self.drawRightString(572, 32, page_text)
        self.restoreState()

def create_super_admin_pdf(output_path):
    doc = SimpleDocTemplate(
        output_path,
        pagesize=letter,
        leftMargin=40,
        rightMargin=40,
        topMargin=50,
        bottomMargin=55
    )

    styles = getSampleStyleSheet()

    # Custom Palette
    PRIMARY = colors.HexColor("#1E2746")     # Dark Navy
    ACCENT = colors.HexColor("#4B55C8")      # Royal Blue
    SECONDARY = colors.HexColor("#627094")   # Muted Slate
    BG_LIGHT = colors.HexColor("#F8FAFF")    # Soft Ice Blue
    BORDER_COLOR = colors.HexColor("#E2E8F0")# Light Border
    SUCCESS_COLOR = colors.HexColor("#0D9488")
    TEXT_DARK = colors.HexColor("#0F172A")

    # Typography Styles
    title_style = ParagraphStyle(
        'DocTitle',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=24,
        leading=28,
        textColor=PRIMARY,
        spaceAfter=6
    )
    subtitle_style = ParagraphStyle(
        'DocSubtitle',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=11,
        leading=15,
        textColor=ACCENT,
        spaceAfter=15
    )
    h1_style = ParagraphStyle(
        'Heading1_Custom',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=14,
        leading=18,
        textColor=PRIMARY,
        spaceBefore=14,
        spaceAfter=6,
        keepWithNext=True
    )
    h2_style = ParagraphStyle(
        'Heading2_Custom',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=11,
        leading=14,
        textColor=ACCENT,
        spaceBefore=10,
        spaceAfter=4,
        keepWithNext=True
    )
    body_style = ParagraphStyle(
        'Body_Custom',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=9,
        leading=13,
        textColor=TEXT_DARK,
        spaceAfter=6
    )
    body_bold = ParagraphStyle(
        'Body_Bold_Custom',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=9,
        leading=13,
        textColor=PRIMARY,
    )
    bullet_style = ParagraphStyle(
        'Bullet_Custom',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=8.5,
        leading=12.5,
        textColor=TEXT_DARK,
        leftIndent=12,
        spaceAfter=3
    )
    callout_style = ParagraphStyle(
        'Callout_Text',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=8.5,
        leading=12.5,
        textColor=PRIMARY
    )
    table_text = ParagraphStyle(
        'TableText',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=8,
        leading=11,
        textColor=TEXT_DARK
    )
    table_header = ParagraphStyle(
        'TableHead',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=8.5,
        leading=11,
        textColor=colors.white
    )

    story = []

    # -------------------------------------------------------------
    # COVER / HEADER BLOCK
    # -------------------------------------------------------------
    story.append(Paragraph("MEDISTORE SAAS PLATFORM", subtitle_style))
    story.append(Paragraph("Super Admin Complete Architecture & Workflow Guide", title_style))
    story.append(Paragraph("A comprehensive multi-tenant SaaS operational manual covering platform-level governance, store provisioning, subscription billing, audit security, and system telemetry.", body_style))
    story.append(Spacer(1, 8))

    # Meta banner table
    meta_data = [
        [
            Paragraph("<b>Role:</b> Super Administrator", table_text),
            Paragraph("<b>Target Domain:</b> Platform SaaS Governance", table_text),
            Paragraph("<b>Architecture:</b> Multi-Tenant Laravel + MySQL", table_text),
            Paragraph("<b>Version:</b> 1.0 (Production)", table_text),
        ]
    ]
    meta_table = Table(meta_data, colWidths=[133, 145, 154, 100])
    meta_table.setStyle(TableStyle([
        ('BACKGROUND', (0, 0), (-1, -1), BG_LIGHT),
        ('BOX', (0, 0), (-1, -1), 1, BORDER_COLOR),
        ('INNERGRID', (0, 0), (-1, -1), 0.5, BORDER_COLOR),
        ('PADDING', (0, 0), (-1, -1), 6),
        ('VALIGN', (0, 0), (-1, -1), 'MIDDLE'),
    ]))
    story.append(meta_table)
    story.append(Spacer(1, 14))

    # -------------------------------------------------------------
    # 1. CORE CONCEPT & ROLE SEPARATION
    # -------------------------------------------------------------
    story.append(Paragraph("1. Core Philosophy & Role Separation", h1_style))
    story.append(HRFlowable(width="100%", thickness=1.5, color=ACCENT, spaceAfter=8))
    
    story.append(Paragraph("Super Admin kisi ek medical store ka manager nahi hai — wo poore multi-tenant SaaS cloud platform ka global owner/administrator hai.", body_style))
    
    role_box_data = [
        [
            Paragraph("<b>Entity Role</b>", table_header),
            Paragraph("<b>Scope & Operational Boundary</b>", table_header),
            Paragraph("<b>Data Access Isolation</b>", table_header),
        ],
        [
            Paragraph("<b>Super Admin</b>", body_bold),
            Paragraph("Poore software infrastructure, stores, billing plans, subscriptions, global settings aur compliance audits ka manager.", table_text),
            Paragraph("Cross-tenant global visibility across all medical stores.", table_text),
        ],
        [
            Paragraph("<b>Store Owner</b>", body_bold),
            Paragraph("Apne specific medical store (pharmacy) ka owner. Inventory, POS billing, purchases, staff aur medicine records chalata hai.", table_text),
            Paragraph("Strict multi-tenant isolation (sirf apne store ka data access kar sakta hai).", table_text),
        ],
        [
            Paragraph("<b>Staff (Optional)</b>", body_bold),
            Paragraph("Store owner ke under employees (Cashier, Billing operator, Pharmacist). Agar owner akela hai to staff banana compulsory nahi hai.", table_text),
            Paragraph("Owner-delegated permissions inside tenant store.", table_text),
        ],
        [
            Paragraph("<b>Customer (Optional)</b>", body_bold),
            Paragraph("Walk-in retail customer. Har sale ke liye customer account compulsory nahi hai; credit/prescription tracking ke liye save hota hai.", table_text),
            Paragraph("Store-level customer ledger.", table_text),
        ],
    ]
    role_table = Table(role_box_data, colWidths=[110, 242, 180])
    role_table.setStyle(TableStyle([
        ('BACKGROUND', (0, 0), (-1, 0), PRIMARY),
        ('ROWBACKGROUNDS', (0, 1), (-1, -1), [colors.white, BG_LIGHT]),
        ('BOX', (0, 0), (-1, -1), 1, BORDER_COLOR),
        ('INNERGRID', (0, 0), (-1, -1), 0.5, BORDER_COLOR),
        ('PADDING', (0, 0), (-1, -1), 5),
        ('VALIGN', (0, 0), (-1, -1), 'TOP'),
    ]))
    story.append(role_table)
    story.append(Spacer(1, 14))

    # -------------------------------------------------------------
    # 2. COMPLETE SUPER ADMIN MODULES BREAKDOWN (1 TO 11)
    # -------------------------------------------------------------
    story.append(Paragraph("2. Super Admin Modules & Technical Architecture", h1_style))
    story.append(HRFlowable(width="100%", thickness=1.5, color=ACCENT, spaceAfter=8))

    modules = [
        ("1. Super Admin Authentication (/super-admin/login)", [
            "<b>Isolated Entry Point:</b> Standard store users cannot login via Super Admin portal.",
            "<b>No Public Registration:</b> Super Admin accounts are seeded / protected server-side.",
            "<b>Security Rules:</b> Inactivity session termination, rate limiting, and automated auth audit logging.",
        ]),
        ("2. Executive Super Admin Dashboard (/super-admin/dashboard)", [
            "<b>Real Database KPIs:</b> Total Stores, Active Stores, Inactive Stores, Expiring Subscriptions, Monthly Revenue.",
            "<b>Recent Directory:</b> Newly provisioned stores with owner linkage.",
            "<b>Live Activity Stream:</b> Powered by real-time `audit_logs` model timeline.",
        ]),
        ("3. Medical Store Management (/super-admin/stores)", [
            "<b>Sequential Code Generator:</b> Automated unique store code (e.g. <code>MED-000001</code>, <code>MED-000002</code>).",
            "<b>Store Directory & Filter:</b> Search by name/code, city filter, status toggle (Active, Inactive, Suspended).",
            "<b>Statutory & Regulatory Records:</b> GSTIN number, Drug License No, License Expiry Date, Store Type (Retail / Wholesale).",
            "<b>Brand Assets:</b> Store logo uploads stored securely in public disk.",
        ]),
        ("4. Store Owner Management (/super-admin/store-owners)", [
            "<b>Direct Store Association:</b> Har owner kisi ek valid store se connected hota hai.",
            "<b>Tenant Boundary Guard:</b> Rahul (Sharma Medical) can never see Gupta Pharmacy's data.",
            "<b>One-Click Status Toggle:</b> Activate / Deactivate owner logins instantly.",
        ]),
        ("5. Subscription Plan Engine (/super-admin/subscriptions/plans)", [
            "<b>Tiered Packaging:</b> Basic, Professional, Enterprise, Custom Yearly plans.",
            "<b>Dynamic Usage Quotas:</b> Max Staff limits, Medicine inventory caps, Monthly invoice volume caps, Customer record caps.",
            "<b>Feature Toggles:</b> Inventory, Purchase orders, POS terminal, GST reporting, Advanced analytics.",
        ]),
        ("6. Store Subscription Lifecycle (/super-admin/subscriptions/stores)", [
            "<b>Plan Assignment:</b> Medical store ko desired plan, start date, end date aur trial duration assign karna.",
            "<b>Automatic Status Transition:</b> Trial, Active, Expiring, Expired, Cancelled, Suspended.",
            "<b>Historical Preservation:</b> Store ka complete subscription history and renewal timeline securely stored.",
        ]),
        ("7. Revenue & Payment Management (/super-admin/payments)", [
            "<b>Billing Ledger:</b> Track subscription payments (Amount, Currency, Date, Status).",
            "<b>Multi-Channel Support:</b> UPI, Online Gateway, Net Banking, Credit/Debit Card, Direct Cash, Bank Transfer.",
            "<b>Audited Transaction ID:</b> Generated transaction reference code (e.g. <code>TXN-XXXXXXXX</code>) with status lifecycle (Paid, Pending, Failed, Refunded).",
        ]),
        ("8. Platform Reports & Analytics (/super-admin/reports)", [
            "<b>Overview Analytics:</b> Platform gross revenue, tenant growth velocity, churn rate.",
            "<b>Granular Breakdowns:</b> Store geographic distributions, subscription tier share, payment collections by method.",
            "<b>Early Expiration Alerts:</b> Stores expiring within 7-30 days highlighted for renewal follow-up.",
            "<b>Data Portability:</b> One-click CSV export for external business intelligence and compliance audits.",
        ]),
        ("9. Platform Notification Center (/super-admin/notifications)", [
            "<b>Target Audience Selector:</b> Broadcast to All Medical Stores or Target Specific Store.",
            "<b>Urgency & Types:</b> General, Announcement, Subscription Expiry Warning, Payment Update, System Upgrade.",
            "<b>Delivery Scheduling:</b> Instant dispatch ('Send Now') or Scheduled future date/time dispatch.",
            "<b>Immutability Guard:</b> Published/Sent notifications cannot be mutated to preserve communication audit trail.",
        ]),
        ("10. Centralized System Settings (/super-admin/settings)", [
            "<b>General Settings:</b> Application Name, Brand Tagline, Default Timezone (Asia/Kolkata), Currency Symbol (₹), Date/Time Formats.",
            "<b>Branding Assets:</b> Platform Header Logo, Tab Favicon, Login Portal Emblem.",
            "<b>Corporate Footprint:</b> SaaS vendor legal name, support email desk, corporate address.",
            "<b>System Operations:</b> Maintenance Mode toggle with customizable tenant downtime message (Super Admin bypass preserved).",
            "<b>Security Policies:</b> Session timeout minutes, password complexity rules, brute-force rate limiter.",
            "<b>Instant Cache Sync:</b> Key-value settings layer with automatic Redis/File cache invalidation upon saving.",
        ]),
        ("11. Immutable Audit Trail System (/super-admin/audit-logs)", [
            "<b>Activity Ledger:</b> Chronological security trace of all Super Admin operations.",
            "<b>Sensitive Redaction:</b> Passwords, auth tokens, secrets are permanently stripped before storage.",
            "<b>Client Telemetry:</b> IP address, browser user-agent, target morph subject reference.",
            "<b>Field-Level Diff Inspector:</b> Side-by-side comparison of Previous Value (Old) vs Updated Value (New).",
        ]),
    ]

    for title, points in modules:
        mod_flow = []
        mod_flow.append(Paragraph(title, h2_style))
        for pt in points:
            mod_flow.append(Paragraph(f"• {pt}", bullet_style))
        story.append(KeepTogether(mod_flow))
        story.append(Spacer(1, 4))

    story.append(Spacer(1, 10))

    # -------------------------------------------------------------
    # 3. PRACTICAL END-TO-END WORKFLOW EXAMPLE
    # -------------------------------------------------------------
    story.append(Paragraph("3. Practical End-to-End Onboarding Walkthrough", h1_style))
    story.append(HRFlowable(width="100%", thickness=1.5, color=ACCENT, spaceAfter=8))

    story.append(Paragraph("Maan lo ek naya medical store — <b>'Sharma Medical Store' (Varanasi)</b> software me join karta hai. Super Admin ka step-by-step workflow:", body_style))

    flow_steps = [
        ("Step 1: Store Provisioning", "Super Admin navigates to <b>Stores → Add Store</b>. Inputs Store Name (<i>Sharma Medical Store</i>), City (<i>Varanasi</i>), Drug License, GSTIN. System auto-generates <code>MED-000001</code>."),
        ("Step 2: Store Owner Creation", "Super Admin navigates to <b>Store Owners → Add Owner</b>. Enters Name (<i>Rahul Sharma</i>), Email (<i>rahul@gmail.com</i>), links to <i>Sharma Medical Store</i>, sets temporary password."),
        ("Step 3: Plan Selection", "Super Admin verifies available plans under <b>Subscription Plans</b> (e.g. <i>Professional Plan @ ₹999/month</i>)."),
        ("Step 4: Subscription Assignment", "Super Admin goes to <b>Store Subscriptions → Assign Plan</b>. Selects <i>Sharma Medical Store</i>, assigns <i>Professional Plan</i> for 1 month / 1 year."),
        ("Step 5: Payment Recording", "Super Admin records the payment of <i>₹999</i> under <b>Payments → Record Payment</b> via UPI with transaction ID. Status marked as <i>Paid</i>."),
        ("Step 6: Tenant Dashboard Access", "Rahul logs in at <code>/store/login</code>. He accesses his private tenant portal to manage medicines, inventory, billing, purchases, and sales in complete isolation."),
    ]

    for s_title, s_desc in flow_steps:
        step_table_data = [
            [
                Paragraph(f"<b>{s_title}</b>", body_bold),
                Paragraph(s_desc, table_text)
            ]
        ]
        step_table = Table(step_table_data, colWidths=[150, 382])
        step_table.setStyle(TableStyle([
            ('BACKGROUND', (0, 0), (0, 0), BG_LIGHT),
            ('BACKGROUND', (1, 0), (1, 0), colors.white),
            ('BOX', (0, 0), (-1, -1), 1, BORDER_COLOR),
            ('INNERGRID', (0, 0), (-1, -1), 0.5, BORDER_COLOR),
            ('PADDING', (0, 0), (-1, -1), 5),
            ('VALIGN', (0, 0), (-1, -1), 'MIDDLE'),
        ]))
        story.append(step_table)
        story.append(Spacer(1, 3))

    story.append(Spacer(1, 10))

    # -------------------------------------------------------------
    # 4. SOLO PHARMACIST & FLEXIBLE ARCHITECTURE
    # -------------------------------------------------------------
    story.append(Paragraph("4. Solo Owner & Walk-in Customer Flexibility", h1_style))
    story.append(HRFlowable(width="100%", thickness=1.5, color=ACCENT, spaceAfter=8))

    story.append(Paragraph("Medical store businesses varying scale ke hote hain. Architecture me ye do flexibilities inbuilt hain:", body_style))

    solo_data = [
        [
            Paragraph("<b>Scenario</b>", table_header),
            Paragraph("<b>Architectural Handling</b>", table_header),
            Paragraph("<b>System Rule</b>", table_header),
        ],
        [
            Paragraph("<b>Single Owner Pharmacy</b><br/>(Owner khud sab manage karta hai)", body_bold),
            Paragraph("Owner login karke directly Billing POS, Stock Entry, Purchase Order aur Reports execute karega.", table_text),
            Paragraph("Staff account create karna <b>OPTIONAL</b> hai. No mandatory staffing dependency.", table_text),
        ],
        [
            Paragraph("<b>Multi-Staff Pharmacy</b><br/>(Cashier, Pharmacist alag hain)", body_bold),
            Paragraph("Owner apne panel se staff members create karke specific module permissions assign karega.", table_text),
            Paragraph("Owner remains supervisor; staff accounts are scoped to owner's store.", table_text),
        ],
        [
            Paragraph("<b>Walk-in Retail Sales</b><br/>(OTC Customers)", body_bold),
            Paragraph("POS counter par fast checkout ke liye customer mobile/name optional hai. Bill standard <i>'Walk-in Customer'</i> record par ban sakta hai.", table_text),
            Paragraph("Customer ledger record tabhi compulsory hoga jab udhaar (credit) ya prescription history maintain karni ho.", table_text),
        ],
    ]
    solo_table = Table(solo_data, colWidths=[130, 232, 170])
    solo_table.setStyle(TableStyle([
        ('BACKGROUND', (0, 0), (-1, 0), PRIMARY),
        ('ROWBACKGROUNDS', (0, 1), (-1, -1), [colors.white, BG_LIGHT]),
        ('BOX', (0, 0), (-1, -1), 1, BORDER_COLOR),
        ('INNERGRID', (0, 0), (-1, -1), 0.5, BORDER_COLOR),
        ('PADDING', (0, 0), (-1, -1), 5),
        ('VALIGN', (0, 0), (-1, -1), 'TOP'),
    ]))
    story.append(solo_table)
    story.append(Spacer(1, 14))

    # -------------------------------------------------------------
    # 5. SITEMAP & SIDEBAR HIERARCHY
    # -------------------------------------------------------------
    story.append(Paragraph("5. Super Admin Navigation Sitemap Hierarchy", h1_style))
    story.append(HRFlowable(width="100%", thickness=1.5, color=ACCENT, spaceAfter=8))

    sitemap_data = [
        [
            Paragraph("<b>Core Administrative Menu</b>", table_header),
            Paragraph("<b>Target URL Route</b>", table_header),
            Paragraph("<b>Primary Functional Objective</b>", table_header),
        ],
        [Paragraph("📊 <b>Dashboard</b>", body_bold), Paragraph("<code>/super-admin/dashboard</code>", table_text), Paragraph("Global SaaS metrics, revenue overview & recent activities", table_text)],
        [Paragraph("🏥 <b>Stores</b>", body_bold), Paragraph("<code>/super-admin/stores</code>", table_text), Paragraph("Medical store provisioning, legal compliance & status control", table_text)],
        [Paragraph("👤 <b>Store Owners</b>", body_bold), Paragraph("<code>/super-admin/store-owners</code>", table_text), Paragraph("Owner account registration, store association & credentials", table_text)],
        [Paragraph("📦 <b>Subscription Plans</b>", body_bold), Paragraph("<code>/super-admin/subscriptions/plans</code>", table_text), Paragraph("Plan packaging, feature toggles & quota limit configuration", table_text)],
        [Paragraph("📋 <b>Store Subscriptions</b>", body_bold), Paragraph("<code>/super-admin/subscriptions/stores</code>", table_text), Paragraph("Assigning subscriptions, trial rules & renewal tracking", table_text)],
        [Paragraph("💳 <b>Payments</b>", body_bold), Paragraph("<code>/super-admin/payments</code>", table_text), Paragraph("Tracking transaction receipts, payment methods & statuses", table_text)],
        [Paragraph("📈 <b>Reports & Analytics</b>", body_bold), Paragraph("<code>/super-admin/reports</code>", table_text), Paragraph("Store growth velocity, revenue charts & CSV export tools", table_text)],
        [Paragraph("🔔 <b>Notifications</b>", body_bold), Paragraph("<code>/super-admin/notifications</code>", table_text), Paragraph("Platform announcements, targeting & scheduling dispatch", table_text)],
        [Paragraph("⚙️ <b>System Settings</b>", body_bold), Paragraph("<code>/super-admin/settings</code>", table_text), Paragraph("Global platform parameters, branding, security & maintenance", table_text)],
        [Paragraph("📋 <b>Audit Logs</b>", body_bold), Paragraph("<code>/super-admin/audit-logs</code>", table_text), Paragraph("Immutable security event ledger & before/after diff inspector", table_text)],
    ]
    sitemap_table = Table(sitemap_data, colWidths=[140, 162, 230])
    sitemap_table.setStyle(TableStyle([
        ('BACKGROUND', (0, 0), (-1, 0), PRIMARY),
        ('ROWBACKGROUNDS', (0, 1), (-1, -1), [colors.white, BG_LIGHT]),
        ('BOX', (0, 0), (-1, -1), 1, BORDER_COLOR),
        ('INNERGRID', (0, 0), (-1, -1), 0.5, BORDER_COLOR),
        ('PADDING', (0, 0), (-1, -1), 4.5),
        ('VALIGN', (0, 0), (-1, -1), 'MIDDLE'),
    ]))
    story.append(sitemap_table)
    story.append(Spacer(1, 14))

    # -------------------------------------------------------------
    # 6. SUMMARY CARD & SIGN OFF
    # -------------------------------------------------------------
    story.append(Paragraph("6. Golden Principles to Remember", h1_style))
    story.append(HRFlowable(width="100%", thickness=1.5, color=ACCENT, spaceAfter=8))

    summary_box_data = [
        [
            Paragraph("""
            <b>Key Architecture Takeaways:</b><br/>
            1. <b>Super Admin</b> = Poore platform ka global administrator (never belongs to a single store).<br/>
            2. <b>Store Owner</b> = Apne medical store ka private owner (has full tenant isolation).<br/>
            3. <b>Staff Management</b> = Optional capability (Solo owners can operate without staff).<br/>
            4. <b>Customer Record</b> = Optional on counter POS (Walk-in sales do not require mandatory registration).<br/>
            5. <b>Audit Logging</b> = Comprehensive, automatic, and read-only for compliance and governance.
            """, callout_style)
        ]
    ]
    summary_table = Table(summary_box_data, colWidths=[532])
    summary_table.setStyle(TableStyle([
        ('BACKGROUND', (0, 0), (-1, -1), BG_LIGHT),
        ('BOX', (0, 0), (-1, -1), 1.5, ACCENT),
        ('PADDING', (0, 0), (-1, -1), 8),
    ]))
    story.append(summary_table)

    # Build PDF
    doc.build(story, canvasmaker=NumberedCanvas)
    print(f"PDF successfully generated at: {output_path}")

if __name__ == "__main__":
    output = sys.argv[1] if len(sys.argv) > 1 else "Super_Admin_Guide_and_Architecture.pdf"
    create_super_admin_pdf(output)
