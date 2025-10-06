<?php
/**
 * Admin Setup Script
 * This script provides information about the new setup process
 */

require_once 'config/database.php';
require_once 'config/auth.php';

// Check if we're running from command line or web
$isCLI = php_sapi_name() === 'cli';

if (!$isCLI) {
    echo "<h1>🔧 Admin Setup - Overhoren</h1>";
    echo "<p>Deze pagina helpt je bij het instellen van de admin credentials.</p>";
}

if ($isCLI) {
    echo "🔧 Overhoren Admin Setup\n";
    echo "=======================\n\n";
    echo "🎯 NIEUWE SETUP PROCES:\n";
    echo "1. Ga naar http://<host>/admin\n";
    echo "2. Je wordt automatisch doorgestuurd naar de setup pagina\n";
    echo "3. Maak je eerste admin gebruiker aan\n";
    echo "4. Je wordt automatisch ingelogd en doorgestuurd naar het beheerpaneel\n\n";
    echo "⚠️  BELANGRIJK: Kies een sterk wachtwoord!\n";
    echo "📝 Na de setup kun je extra gebruikers toevoegen via het beheerpaneel.\n\n";
} else {
    echo "<div style='max-width: 600px; margin: 20px auto; padding: 20px; background: #f8f9fa; border-radius: 8px;'>";
    echo "<h2>🎯 Nieuwe Setup Proces</h2>";
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 6px; margin: 15px 0;'>";
    echo "<strong>✅ Eenvoudige Setup:</strong><br>";
    echo "1. Ga naar <a href='/admin'>het beheerpaneel</a><br>";
    echo "2. Je wordt automatisch doorgestuurd naar de setup pagina<br>";
    echo "3. Maak je eerste admin gebruiker aan<br>";
    echo "4. Je wordt automatisch ingelogd en doorgestuurd naar het beheerpaneel";
    echo "</div>";
    
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 6px; margin: 15px 0;'>";
    echo "<strong>⚠️ Belangrijk:</strong><br>";
    echo "• Kies een sterk wachtwoord (minimaal 6 karakters)<br>";
    echo "• Je kunt later extra gebruikers toevoegen via het beheerpaneel<br>";
    echo "• De setup is alleen beschikbaar als er nog geen admin gebruikers bestaan";
    echo "</div>";
    
    echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 6px; margin: 15px 0;'>";
    echo "<strong>🔐 Database-based Authentication:</strong><br>";
    echo "• Wachtwoorden worden veilig gehashed opgeslagen in de database<br>";
    echo "• Meerdere admin gebruikers mogelijk<br>";
    echo "• Gebruikers kunnen worden beheerd via het beheerpaneel";
    echo "</div>";
    
    echo "<h3>🔗 Links</h3>";
    echo "<ul>";
    echo "<li><a href='/admin'>Beheerpaneel (start hier voor setup)</a></li>";
    echo "<li><a href='/login'>Inloggen (na setup)</a></li>";
    echo "<li><a href='/'>Hoofdpagina</a></li>";
    echo "</ul>";
    
    echo "<h3>🛡️ Beveiligingsfuncties</h3>";
    echo "<ul>";
    echo "<li>Session-based authenticatie</li>";
    echo "<li>Rate limiting tegen brute force aanvallen</li>";
    echo "<li>Automatische session timeout (30 minuten)</li>";
    echo "<li>XSS bescherming</li>";
    echo "<li>CSRF token ondersteuning</li>";
    echo "</ul>";
    
    echo "</div>";
}

// Test database connection and show status
try {
    $stmt = executeQuery($pdo, 'SELECT COUNT(*) as count FROM tests');
    $result = $stmt->fetch();
    $testCount = $result['count'];
    
    // Check if any admin users exist
    $adminCount = hasAdminUsers() ? 1 : 0;
    
    if ($isCLI) {
        echo "✅ Database verbinding succesvol\n";
        echo "📊 Aantal toetsen in database: {$testCount}\n";
        echo "👤 Admin gebruikers: " . ($adminCount > 0 ? "{$adminCount} gebruiker(s) bestaan" : "Geen gebruikers - setup vereist") . "\n\n";
    } else {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 6px; margin: 15px 0;'>";
        echo "✅ Database verbinding succesvol<br>";
        echo "📊 Aantal toetsen in database: {$testCount}<br>";
        echo "👤 Admin gebruikers: " . ($adminCount > 0 ? "{$adminCount} gebruiker(s) bestaan" : "Geen gebruikers - setup vereist");
        echo "</div>";
    }
    
} catch (Exception $e) {
    if ($isCLI) {
        echo "❌ Database fout: " . $e->getMessage() . "\n";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 6px; margin: 15px 0;'>";
        echo "❌ Database fout: " . htmlspecialchars($e->getMessage());
        echo "</div>";
    }
}

if (!$isCLI) {
    echo "<style>";
    echo "body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }";
    echo "code { background: #e9ecef; padding: 2px 6px; border-radius: 3px; }";
    echo "a { color: #007bff; text-decoration: none; }";
    echo "a:hover { text-decoration: underline; }";
    echo "</style>";
}
?>
