<form id="frm-contato" action="/ajax/enviar-contato" method="post">
    <div class="row">
        <div class="col-md-12 mb-2">
            <input type="text" name="nome" required="" class="form-control" value="" placeholder="Seu nome" />
        </div>
        <div class="col-md-12 mb-2">
            <input type="email" name="email" required="" class="form-control" value="" placeholder="Seu e-mail" />
        </div>
        <!-- <div class="col-md-12">
    <input type="tel" name="celular" {event_celular} placeholder="Seu whatsapp" />
    </div> -->
        <div class="col-3 pr-0">
            <!-- arr[1] -->
            <select name="ddi" class="form-control" id="ddi">
                <option value="93"> Afghanistão +93</option>
                <option value="27"> África do Sul +27</option>
                <option value="355"> Albânia +355</option>
                <option value="49"> Alemanha +49</option>
                <option value="376"> Andora +376</option>
                <option value="244"> Angola +244</option>
                <option value="1"> Anguilla +1</option>
                <option value="672"> Antarctica +672</option>
                <option value="1"> Antígua e Barbuda +1</option>
                <option value="54"> Argentina +54</option>
                <option value="213"> Argélia +213</option>
                <option value="374"> Arménia +374</option>
                <option value="297"> Aruba +297</option>
                <option value="966"> Arábia Saudita +966</option>
                <option value="61"> Austrália +61</option>
                <option value="43"> Áustria +43</option>
                <option value="994"> Azerbaijão +994</option>
                <option value="1"> Bahamas +1</option>
                <option value="973"> Bahrein +973</option>
                <option value="880"> Bangladesh +880</option>
                <option value="1"> Barbados +1</option>
                <option value="32"> Bélgica +32</option>
                <option value="501"> Belize +501</option>
                <option value="229"> Benim +229</option>
                <option value="1"> Bermuda +1</option>
                <option value="975"> Butão +975</option>
                <option value="375"> Bielorrússia +375</option>
                <option value="591"> Bolívia +591</option>
                <option value="387"> Bósnia e Herzegovina +387</option>
                <option value="267"> Botswana +267</option>
                <option value="55" selected=""> Brasil +55</option>
                <option value="0"> British Indian Ocean Territory +0</option>
                <option value="673"> Brunei +673</option>
                <option value="359"> Bulgária +359</option>
                <option value="226"> Burkina Faso +226</option>
                <option value="95"> Myanmar (Burma) +95</option>
                <option value="257"> Burundi +257</option>
                <option value="237"> Camarões +237</option>
                <option value="238"> Cabo Verde +238</option>
                <option value="855"> Camboja +855</option>
                <option value="1"> Canadá +1</option>
                <option value="974"> Catar +974</option>
                <option value="235"> Chad +235</option>
                <option value="56"> Chile +56</option>
                <option value="86"> China +86</option>
                <option value="357"> Chipre +357</option>
                <option value="61"> Christmas Island +61</option>
                <option value="65"> Cingapura +65</option>
                <option value="57"> Colômbia +57</option>
                <option value="269"> Comores +269</option>
                <option value="850"> Coreia do Norte +850</option>
                <option value="82"> Coreia do Sul +82</option>
                <option value="225"> Costa do Marfim +225</option>
                <option value="506"> Costa Rica +506</option>
                <option value="385"> Croácia +385</option>
                <option value="53"> Cuba +53</option>
                <option value="45"> Dinamarca +45</option>
                <option value="253"> Djibouti +253</option>
                <option value="1"> Dominica +1</option>
                <option value="20"> Egito +20</option>
                <option value="503"> El Salvador +503</option>
                <option value="971"> Emirados Árabes Unidos +971</option>
                <option value="593"> Equador +593</option>
                <option value="291"> Eritreia +291</option>
                <option value="372"> Estónia +372</option>
                <option value="34"> Espanha +34</option>
                <option value="251"> Etiópia +251</option>
                <option value="679"> Fiji +679</option>
                <option value="358"> Finlândia +358</option>
                <option value="33"> França +33</option>
                <option value="241"> Gabão +241</option>
                <option value="220"> Gâmbia +220</option>
                <option value="970"> Banda de Gaza (Palestina) +970</option>
                <option value="995"> Geórgia +995</option>
                <option value="233"> Gana +233</option>
                <option value="350"> Gibraltar +350</option>
                <option value="30"> Grécia +30</option>
                <option value="299"> Groelândia +299</option>
                <option value="1"> Granada +1</option>
                <option value="1"> Guam +1</option>
                <option value="502"> Guatemala +502</option>
                <option value="592"> Guiana +592</option>
                <option value="224"> Guiné +224</option>
                <option value="240"> Guiné Equatorial +240</option>
                <option value="245"> Guiné-Bissau +245</option>
                <option value="509"> Haiti +509</option>
                <option value="504"> Honduras +504</option>
                <option value="852"> Hong Kong +852</option>
                <option value="36"> Hungria +36</option>
                <option value="354"> Islândia +354</option>
                <option value="1"> Ilhas Cayman +1</option>
                <option value="61"> Ilhas Cocos (Keeling) +61</option>
                <option value="44"> Ilha de Man +44</option>
                <option value="682"> Ilhas Cook +682</option>
                <option value="500"> Ilhas Falkland (Malvinas) +500</option>
                <option value="298"> Ilhas Feroé +298</option>
                <option value="1"> Ilhas Mariana do Norte +1</option>
                <option value="692"> Ilhas Marshall +692</option>
                <option value="672"> Ilhas Norfolk +672</option>
                <option value="870"> Ilhas Pitcairn +870</option>
                <option value="677"> Ilhas Salomão +677</option>
                <option value="1"> Ilhas Turcas e Caicos +1</option>
                <option value="1"> Ilhas Virgens Americanas +1</option>
                <option value="1"> Ilhas Virgens Britânicas +1</option>
                <option value="91"> India +91</option>
                <option value="62"> Indonésia +62</option>
                <option value="44"> Inglaterra (Reino Unido) +44</option>
                <option value="98"> Irã +98</option>
                <option value="964"> Iraque +964</option>
                <option value="353"> Irlanda +353</option>
                <option value="972"> Israel +972</option>
                <option value="39"> Itália +39</option>
                <option value="1"> Jamaica +1</option>
                <option value="81"> Japão +81</option>
                <option value="0"> Jersey +0</option>
                <option value="962"> Jordânia +962</option>
                <option value="7"> Cazaquistão +7</option>
                <option value="254"> Quénia (Kenya) +254</option>
                <option value="686"> Kiribati +686</option>
                <option value="381"> Kosovo +381</option>
                <option value="965"> Kuwait +965</option>
                <option value="996"> Quirguistão +996</option>
                <option value="856"> Laos +856</option>
                <option value="371"> Letônia +371</option>
                <option value="961"> Líbano +961</option>
                <option value="266"> Lesoto +266</option>
                <option value="231"> Libéria +231</option>
                <option value="218"> Líbia +218</option>
                <option value="423"> Liechtenstein +423</option>
                <option value="370"> Lituânia +370</option>
                <option value="352"> Luxemburgo +352</option>
                <option value="853"> Macau +853</option>
                <option value="389"> Macedónia +389</option>
                <option value="261"> Madagáscar +261</option>
                <option value="265"> Malawi +265</option>
                <option value="60"> Malásia +60</option>
                <option value="960"> Maldivas +960</option>
                <option value="223"> Mali +223</option>
                <option value="356"> Malta +356</option>
                <option value="222"> Mauritânia +222</option>
                <option value="230"> Maurícia +230</option>
                <option value="262"> Mayotte +262</option>
                <option value="52"> México +52</option>
                <option value="691"> Micronésia +691</option>
                <option value="373"> Moldávia +373</option>
                <option value="377"> Monaco +377</option>
                <option value="976"> Mongólia +976</option>
                <option value="382"> Montenegro +382</option>
                <option value="1"> Montserrat +1</option>
                <option value="212"> Marrocos +212</option>
                <option value="258"> Moçambique +258</option>
                <option value="264"> Namíbia +264</option>
                <option value="674"> Nauru +674</option>
                <option value="977"> Nepal +977</option>
                <option value="599"> Netherlands Antilles +599</option>
                <option value="687"> Nova Caledônia +687</option>
                <option value="64"> Nova Zelândia +64</option>
                <option value="505"> Nicaragua +505</option>
                <option value="227"> Níger +227</option>
                <option value="234"> Nigéria +234</option>
                <option value="683"> Niue +683</option>
                <option value="47"> Noruega +47</option>
                <option value="968"> Omã +968</option>
                <option value="31"> Países Baixos +31</option>
                <option value="92"> Paquistão +92</option>
                <option value="680"> Palau +680</option>
                <option value="507"> Panamá +507</option>
                <option value="675"> Papua-Nova Guiné +675</option>
                <option value="595"> Paraguai +595</option>
                <option value="51"> Peru +51</option>
                <option value="63"> Filipinas +63</option>
                <option value="48"> Polónia +48</option>
                <option value="689"> Polinésia Francesa +689</option>
                <option value="351"> Portugal +351</option>
                <option value="1"> Porto Rico +1</option>
                <option value="242"> República do Congo +242</option>
                <option value="243"> República Democrática do Congo +243</option>
                <option value="236"> República Centro-Africana +236</option>
                <option value="420"> República Checa +420</option>
                <option value="1"> República Dominicana +1</option>
                <option value="40"> Roménia +40</option>
                <option value="250"> Ruanda +250</option>
                <option value="7"> Rússia +7</option>
                <option value="590"> Saint Barthelemy +590</option>
                <option value="685"> Samoa +685</option>
                <option value="1"> Samoa Americana +1</option>
                <option value="378"> San Marino +378</option>
                <option value="239"> Sao Tome e Principe +239</option>
                <option value="221"> Senegal +221</option>
                <option value="381"> Serbia +381</option>
                <option value="232"> Serra Leoa +232</option>
                <option value="248"> Seychelles +248</option>
                <option value="421"> Slovakia +421</option>
                <option value="386"> Slovenia +386</option>
                <option value="252"> Somalia +252</option>
                <option value="94"> Sri Lanka +94</option>
                <option value="290"> Saint Helena +290</option>
                <option value="1"> Saint Kitts and Nevis +1</option>
                <option value="1"> Saint Lucia +1</option>
                <option value="1"> Saint Martin +1</option>
                <option value="508"> Saint Pierre and Miquelon +508</option>
                <option value="1"> Saint Vincent and the Grenadines +1</option>
                <option value="249"> Sudão +249</option>
                <option value="597"> Suriname +597</option>
                <option value="0"> Svalbard +0</option>
                <option value="268"> Suazilândia +268</option>
                <option value="46"> Suécia +46</option>
                <option value="41"> Suiça +41</option>
                <option value="963"> Syria +963</option>
                <option value="886"> Taiwan +886</option>
                <option value="992"> Tajiquistão +992</option>
                <option value="255"> Tanzânia +255</option>
                <option value="670"> Timor-Leste +670</option>
                <option value="66"> Tailândia +66</option>
                <option value="228"> Togo +228</option>
                <option value="690"> Tokelau +690</option>
                <option value="676"> Tonga +676</option>
                <option value="1"> Trinidad e Tobago +1</option>
                <option value="216"> Tunísia +216</option>
                <option value="993"> Turquemenistão +993</option>
                <option value="90"> Turquia +90</option>
                <option value="688"> Tuvalu +688</option>
                <option value="256"> Uganda +256</option>
                <option value="380"> Ucrânia +380</option>
                <option value="598"> Uruguai +598</option>
                <option value="1"> Estados Unidos (EUA) +1</option>
                <option value="998"> Uzbequistão +998</option>
                <option value="678"> Vanuatu +678</option>
                <option value="39"> Vaticano +39</option>
                <option value="58"> Venezuela +58</option>
                <option value="84"> Vietnã (Vietname) +84</option>
                <option value="681"> Wallis e Futuna +681</option>
                <option value="970"> West Bank +970</option>
                <option value="0"> Western Sahara +0</option>
                <option value="967"> Iémen (Iémen, Yemen) +967</option>
                <option value="263"> Zimbabwe(Zimbabué) +263</option>
                <option value="260"> Zâmbia +260</option>
            </select>
        </div>
        <div class="col-9 pl-0">
            <input type="tel" name="celular" required="" onblur="mask(this,clientes_mascaraTelefone);" onkeypress="mask(this,clientes_mascaraTelefone);" class="form-control" placeholder="Seu whatsapp" />
        </div>
        {{-- <div class="col-12 mb-3">
            <!-- arr[1] -->
            <select name="curso" class="form-control" required="" id="curso">
                <option value="" selected="">Curso de interesse</option>
                <option value="66">Piloto Privado Avião Teórico EAD</option>
                <option value="67">Piloto Comercial Avião IFR EAD</option>
                <option value="69">Piloto Privado Avião Prático ( Triciclo)</option>
                <option value="71">Piloto Comercial Avião MNTE IFR Prático</option>
                <option value="72">Piloto Comercial Avião MLTE IFR Prático</option>
                <option value="75">Instrutor de Voo Avião Prático </option>
                <option value="85">Multimotor Prático</option>
                <option value="90">Revalidação de Habilitação Monomotor IFR ou VFR</option>
                <option value="97">Plano de Formação de Piloto Civil Profissional - Ensino Técnico </option>
                <option value="107">Instrutor de Voo Teórico EAD</option>
                <option value="116">Time Building ( Horas Avulsas)</option>
                <option value="117">Revalidação de Habilitação Multimotor IFR ou VFR</option>
                <option value="118">Revalidação de Habilitação Instrutor de Voo</option>
                <option value="126"> Teórico de Piloto de Linha Aérea (EAD)</option>
                <option value="127">Teórico de Jet Training Boeing 737-800</option>
                <option value="128">Plano de Formação - Superior de Ciências Aeronáuticas </option>
            </select>
        </div> --}}
        <div class="col-md-12 mt-2">
            <textarea name="obs" required="" class="form-control" placeholder="Sua mensagem" rows="4"></textarea>
        </div>
        <div class="col-md-12">
            <input type="hidden" name="tag_origem" value="Contato do site leilão" />
            <input type="hidden" name="redirect0" value="https://repasses.leilao.com.br/contato" />
            <input type="hidden" name="redirect1" value="https://repasses.leilao.com.br/obrigado-pelo-contato" />
            <div id="html_element" class="mb-3">
            </div>
            @csrf
        </div>
        <div class="col-md-12">
            <button type="submit" class="btn btn-primary env-form">Enviar</button>
        </div>
    </div>
</form>
<script>
    function sub_contato(){
        var form = $('#frm-contato');
        $(function(){
            form.find('button[type="submit"]').on('click', function(e){
                e.preventDefault();
                submitFormularioCSRF(form,function(res){
                    try {
                        if(res.mens){
                            $('.mens').html(res.mens);
                        }
                        if(res.exec && res.redirect){
                            window.location=res.redirect;
                        }
                        if(res.code_mens=='enc'){
                            if(res.redirect=='self'){
                                var red = urlAtual();
                                window.location=red;
                                console.log(red);
                            }
                        }
                    } catch (error) {
                        console.log(error);
                    }
                },function(e){
                    lib_funError(e);
                    try {
                        if(e.cnpj[0]){
                            alert(e.cnpj[0]+'\nEm caso de dúvida entre em contato com o nosso suporte');
                        }
                    } catch (error) {
                        console.log(error);
                    }
                });
            });
            form.submit(function(e){

            });
        });
    }
    sub_contato();
</script>
