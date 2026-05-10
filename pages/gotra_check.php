<?php
/**
 * गोत्र जाँच — विवाह योग्यता उपकरण
 */
require_once __DIR__ . '/../includes/header.php';

$stmt = $pdo->prepare("SELECT id, pratham_naam, kul_naam, gotra FROM vyakti WHERE parivar_id = ? ORDER BY pratham_naam");
$stmt->execute([getParivarId()]);
$persons = $stmt->fetchAll();
?>

<div class="card">
    <h2 style="text-align: center; color: var(--rang-pramukh);"><i class="fa fa-heart"></i> विवाह योग्यता — गोत्र जाँच</h2>
    <p style="text-align: center; margin-bottom: 2rem; color: #666;">दो व्यक्तियों के बीच गोत्र मिलान जाँचना</p>
    
    <div style="display: flex; gap: 2rem; align-items: center; justify-content: center; flex-wrap: wrap;">
        <div class="form-group" style="flex: 1; min-width: 250px;">
            <label>व्यक्ति A चुनें</label>
            <select id="person-a" onchange="resetResult()">
                <option value="">-- चुनें --</option>
                <?php foreach ($persons as $p): ?>
                    <option value="<?php echo $p['id']; ?>" data-gotra="<?php echo s($p['gotra']); ?>">
                        <?php echo s($p['pratham_naam'] . ' ' . $p['kul_naam']); ?> 
                        (<?php echo s($p['gotra'] ?? 'अज्ञात'); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div style="font-size: 2rem; color: var(--rang-seemant);"><i class="fa fa-arrows-left-right"></i></div>
        
        <div class="form-group" style="flex: 1; min-width: 250px;">
            <label>व्यक्ति B चुनें</label>
            <select id="person-b" onchange="resetResult()">
                <option value="">-- चुनें --</option>
                <?php foreach ($persons as $p): ?>
                    <option value="<?php echo $p['id']; ?>" data-gotra="<?php echo s($p['gotra']); ?>">
                        <?php echo s($p['pratham_naam'] . ' ' . $p['kul_naam']); ?> 
                        (<?php echo s($p['gotra'] ?? 'अज्ञात'); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    
    <div style="text-align: center; margin-top: 1rem;">
        <button onclick="checkGotra()" style="width: auto; padding: 1rem 3rem;">जाँचें</button>
    </div>
    
    <div id="result-container" style="margin-top: 2rem; text-align: center; display: none;">
        <div id="result-card" class="card" style="border-width: 2px;">
            <h3 id="result-text"></h3>
            <p id="result-desc"></p>
        </div>
    </div>

    <div style="margin-top: 3rem; border-top: 1px solid var(--rang-seemant); padding-top: 1.5rem;">
        <h3><i class="fa fa-book"></i> गोत्र की जानकारी</h3>
        <p style="font-size: 0.9rem; color: #666; margin-bottom: 1rem;">गोत्र मिलान हिंदू विवाह परंपरा का एक महत्वपूर्ण हिस्सा है। एक ही गोत्र (सगोत्र) में विवाह को आमतौर पर भाई-बहन का संबंध मानकर वर्जित किया जाता है।</p>
        
        <div id="gotra-info-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem;">
            <!-- Will be populated if needed -->
        </div>
    </div>
    
    <div style="margin-top: 2rem; padding: 1rem; background: #fff8f0; border-radius: 8px; border: 1px dashed var(--rang-pramukh); font-size: 0.85rem; color: #8b4513;">
        <strong>चेतावनी:</strong> यह उपकरण केवल सामान्य जानकारी और सहायता के लिए है। विवाह संबंधी अंतिम निर्णय के लिए कृपया अपने कुल पुरोहित या विद्वान पंडित से परामर्श अवश्य लें।
    </div>
</div>

<script>
    function resetResult() {
        document.getElementById('result-container').style.display = 'none';
    }

    function checkGotra() {
        const a = document.getElementById('person-a');
        const b = document.getElementById('person-b');
        
        if (!a.value || !b.value) {
            alert('कृपया दोनों व्यक्तियों को चुनें।');
            return;
        }
        
        const gotraA = a.options[a.selectedIndex].getAttribute('data-gotra');
        const gotraB = b.options[b.selectedIndex].getAttribute('data-gotra');
        
        const resultContainer = document.getElementById('result-container');
        const resultCard = document.getElementById('result-card');
        const resultText = document.getElementById('result-text');
        const resultDesc = document.getElementById('result-desc');
        
        resultContainer.style.display = 'block';
        
        if (!gotraA || !gotraB) {
            resultCard.style.borderColor = '#eab308'; // Warning
            resultText.innerHTML = '⚠️ जानकारी अपूर्ण';
            resultText.style.color = '#854d0e';
            resultDesc.innerHTML = 'किसी एक या दोनों व्यक्तियों का गोत्र दर्ज नहीं है। कृपया पहले प्रोफ़ाइल में गोत्र भरें।';
        } else if (gotraA.trim() === gotraB.trim()) {
            resultCard.style.borderColor = 'var(--rang-asafal)';
            resultText.innerHTML = '❌ सगोत्र विवाह — अनुमत नहीं';
            resultText.style.color = 'var(--rang-asafal)';
            resultDesc.innerHTML = `दोनों का गोत्र <strong>${gotraA}</strong> है। शास्त्रानुसार एक ही गोत्र में विवाह वर्जित है।`;
        } else {
            resultCard.style.borderColor = 'var(--rang-safal)';
            resultText.innerHTML = '✅ गोत्र अलग — विवाह योग्य';
            resultText.style.color = 'var(--rang-safal)';
            resultDesc.innerHTML = `गोत्र मिलान सफल। व्यक्ति A का गोत्र <strong>${gotraA}</strong> और व्यक्ति B का गोत्र <strong>${gotraB}</strong> है।`;
        }
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
