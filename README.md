# mod_checkin - Check-in da aula

Atividade Moodle simples para registrar presença durante uma janela de tempo.

## Recursos

- Botão **Estou presente** para o aluno.
- Janela de check-in configurável, por exemplo 19:00–19:15.
- Código numérico opcional de 4 ou 6 dígitos, com regeneração pelo professor.
- Restrição opcional ao mesmo endereço IP registrado pelo professor.
- Restrição opcional por geolocalização, usando a posição capturada pelo professor e um raio configurável em metros.
- Relatório com alunos presentes, pendentes/ausentes, horário, IP e localização quando coletada.
- Regra de conclusão da atividade: concluir após check-in bem-sucedido.
- Privacy API para exportação e exclusão de IPs e coordenadas.
- Backup e restore da atividade.
- Idiomas `en` e `pt_br`.

## Compatibilidade

Moodle 4.5 a 5.2.

Em Moodle 5.1 ou superior, os plugins ficam dentro do diretório `public/mod/`. Em versões anteriores, ficam em `mod/`.

## Como funciona o IP

Quando a opção **Exigir o mesmo endereço IP do professor** estiver ativa, o professor abre a atividade na rede da sala e clica em **Usar este IP**. O Moodle grava o endereço IP que ele identifica naquele acesso. O aluno só consegue registrar presença quando o Moodle identifica exatamente o mesmo IP.

Isso é especialmente útil quando professor e alunos estão no mesmo Wi-Fi e saem para a internet pelo mesmo NAT. Não é prova criptográfica de presença: VPNs, proxies, CGNAT e configurações incorretas de proxy reverso podem reduzir a confiabilidade.

## Como funciona a localização

Quando a opção **Exigir localização** estiver ativa, o professor abre a atividade na sala e clica em **Capturar minha localização**. O navegador solicita permissão e envia latitude, longitude e precisão. O professor define um raio permitido em metros.

No check-in do aluno, o navegador solicita a localização e o servidor calcula a distância até o ponto de referência usando a fórmula de Haversine. O registro só é aceito quando a distância calculada está dentro do raio.

A Geolocation API do navegador normalmente exige HTTPS. Coordenadas fornecidas pelo navegador também podem ser falsificadas em um dispositivo controlado pelo usuário; portanto, a localização deve ser tratada como uma barreira contra fraude casual, não como prova absoluta de presença.

## Instalação

Copie a pasta `checkin` para o diretório de módulos do Moodle e execute a atualização normal do site:

```bash
php admin/cli/upgrade.php
```

No Moodle 5.1+ o caminho do CLI fica sob `public/admin/cli/upgrade.php` quando a instalação segue a nova estrutura de diretórios.

## Privacidade

Quando ativadas as respectivas validações, o plugin pode armazenar:

- IP do professor usado como referência;
- localização de referência do professor;
- IP do aluno no momento do check-in;
- latitude, longitude e precisão informadas pelo navegador do aluno;
- distância calculada até a localização de referência.

Esses dados devem ser usados de acordo com a política de privacidade da instituição.
