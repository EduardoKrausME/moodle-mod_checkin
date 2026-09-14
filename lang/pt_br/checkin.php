<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * checkin.php
 *
 * @package   mod_checkin
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['absent'] = 'Sem check-in';
$string['accuracy'] = 'Precisão';
$string['alreadycheckedin'] = 'Sua presença já foi registrada.';
$string['availability'] = 'Janela de check-in';
$string['capturelocation'] = 'Capturar minha localização';
$string['checkin'] = 'Estou presente';
$string['checkin:addinstance'] = 'Adicionar um novo check-in da aula';
$string['checkin:checkin'] = 'Registrar a própria presença';
$string['checkin:manage'] = 'Gerenciar o check-in da aula';
$string['checkin:view'] = 'Visualizar o check-in da aula';
$string['checkin:viewreports'] = 'Visualizar o relatório de presença';
$string['checkinname'] = 'Nome do check-in';
$string['checkinsuccess'] = 'Presença registrada com sucesso.';
$string['code'] = 'Código';
$string['codelength'] = 'Tamanho do código';
$string['coderegenerated'] = 'Um novo código de check-in foi gerado.';
$string['completioncheckin'] = 'O aluno deve realizar o check-in com sucesso';
$string['completiondetail:checkin'] = 'Realizar o check-in com sucesso';
$string['coordinates'] = 'Coordenadas';
$string['currentip'] = 'IP atual';
$string['digits4'] = '4 dígitos';
$string['digits6'] = '6 dígitos';
$string['distance'] = 'Distância';
$string['entercode'] = 'Digite o código mostrado pelo professor';
$string['errorendbeforestart'] = 'O horário de fechamento deve ser posterior ao horário de abertura.';
$string['errorradius'] = 'O raio deve ficar entre 5 e 10000 metros.';
$string['eventattendancemarked'] = 'Check-in registrado';
$string['geolocationerror'] = 'Não foi possível obter sua localização. Autorize o acesso à localização no navegador e tente novamente.';
$string['geolocationunsupported'] = 'Este navegador não oferece suporte à geolocalização.';
$string['invalidcode'] = 'O código de check-in está incorreto.';
$string['ipaddress'] = 'Endereço IP';
$string['ipmismatch'] = 'Seu endereço IP é diferente do IP registrado pelo professor.';
$string['ipregistered'] = 'IP da sala registrado.';
$string['location'] = 'Localização';
$string['locationinvalid'] = 'A localização recebida do navegador é inválida.';
$string['locationnotset'] = 'O professor ainda não capturou a localização da sala.';
$string['locationradius'] = 'Distância máxima da localização do professor (metros)';
$string['locationregistered'] = 'Localização da sala registrada.';
$string['locationrequired'] = 'A localização é obrigatória para este check-in.';
$string['metres'] = 'm';
$string['modulename'] = 'Check-in da aula';
$string['modulenameplural'] = 'Check-ins da aula';
$string['notset'] = 'Não definido';
$string['outsideallowedradius'] = 'Você está fora da área permitida para o check-in.';
$string['pending'] = 'Aguardando';
$string['pluginadministration'] = 'Administração do check-in da aula';
$string['pluginname'] = 'Check-in da aula';
$string['present'] = 'Presente';
$string['privacy:export:record'] = 'Meu check-in';
$string['privacy:export:reference'] = 'Dados de referência registrados por mim';
$string['privacy:metadata:checkin'] = 'Dados de referência de rede e localização do professor usados para validar a presença.';
$string['privacy:metadata:checkin:teacheraccuracy'] = 'Precisão informada para a localização de referência do professor.';
$string['privacy:metadata:checkin:teacherip'] = 'Endereço IP de referência registrado por um professor.';
$string['privacy:metadata:checkin:teacheriptime'] = 'Momento em que o endereço IP de referência foi registrado.';
$string['privacy:metadata:checkin:teacheripuserid'] = 'Usuário que registrou o endereço IP de referência.';
$string['privacy:metadata:checkin:teacherlatitude'] = 'Latitude de referência do professor.';
$string['privacy:metadata:checkin:teacherlocationtime'] = 'Momento em que a localização de referência foi registrada.';
$string['privacy:metadata:checkin:teacherlocationuserid'] = 'Usuário que registrou a localização de referência.';
$string['privacy:metadata:checkin:teacherlongitude'] = 'Longitude de referência do professor.';
$string['privacy:metadata:records'] = 'Registros de presença dos alunos.';
$string['privacy:metadata:records:accuracy'] = 'Precisão da localização informada pelo navegador do usuário.';
$string['privacy:metadata:records:distance'] = 'Distância calculada até a localização de referência do professor.';
$string['privacy:metadata:records:ipaddress'] = 'Endereço IP identificado pelo Moodle durante o check-in.';
$string['privacy:metadata:records:latitude'] = 'Latitude informada pelo navegador do usuário.';
$string['privacy:metadata:records:longitude'] = 'Longitude informada pelo navegador do usuário.';
$string['privacy:metadata:records:timecreated'] = 'Momento em que o usuário realizou o check-in.';
$string['privacy:metadata:records:userid'] = 'Usuário que realizou o check-in.';
$string['privacywarning'] = 'Esta atividade pode armazenar endereço IP e localização precisa quando essas validações estiverem habilitadas.';
$string['radius'] = 'Raio';
$string['regeneratecode'] = 'Gerar novo código';
$string['registeredip'] = 'IP registrado para a sala';
$string['registeredlocation'] = 'Localização registrada para a sala';
$string['registerthisip'] = 'Usar este IP';
$string['report'] = 'Relatório de presença';
$string['requirelocation'] = 'Exigir localização';
$string['requirelocation_help'] = 'O professor captura a localização da sala. O navegador do aluno precisa fornecer uma posição dentro do raio configurado. É necessário HTTPS e permissão de localização no navegador.';
$string['requireteacherip'] = 'Exigir o mesmo endereço IP do professor';
$string['requireteacherip_help'] = 'O professor registra o IP atual dentro da atividade. O aluno só consegue fazer o check-in quando o Moodle identifica o mesmo endereço IP público.';
$string['security'] = 'Validação de presença';
$string['status'] = 'Situação';
$string['student'] = 'Aluno';
$string['teachercontrols'] = 'Controles do professor';
$string['teacheripnotset'] = 'O professor ainda não registrou o endereço IP da sala.';
$string['time'] = 'Horário';
$string['timeend'] = 'Fecha em';
$string['timestart'] = 'Abre em';
$string['totalpresent'] = 'Presentes:  de ';
$string['usecode'] = 'Exigir código numérico';
$string['windowclosed'] = 'O check-in está encerrado.';
$string['windowlabel'] = 'Janela';
$string['windownotstarted'] = 'O check-in ainda não abriu.';
$string['windowopen'] = 'O check-in está aberto';
