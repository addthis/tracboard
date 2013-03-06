<?php
/**
 * Copyright 2011 Clearspring Technologies
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/*
 * Specify configuration details for your team and Trac deployment
 */

/* The Trac milestone that your team uses to represent its backlog (see the README for workflow) */
define("BACKLOG_MILESTONE", "Backlog");

/* Your team's Trac server */
define("TRAC_SERVER", "trac.clearspring.local");

/* What field you use in trac to group teams */
$PIPELINE_FIELD = "pipeline";


/* Your team's pipelines (see the README for workflow) */
$PIPELINES = array(
  "addthis",
  "advertising",
  "internal",
  "clearspring",
  "TBD"
  );

/* Your team (likely a subset of all Trac users) */
$ASSIGNEES = array(
  "abramsm",
  "angel",
  "arunr",
  "cfr",
  "christopher",
  "densil",
  "drew",
  "foo",
  "greg",
  "hugh",
  "jeff",
  "jim",
  "jithin",
  "jorbin",
  "justin",
  "kacy",
  "kori",
  "mano",
  "marty",
  "matt",
  "mkuruvila",
  "myles",
  "nishin",
  "philip",
  "protas",
  "rahf",
  "rich",
  "sherin",
  "stenoien",
  "stephen",
  "stewart",
  "will",
  "yuesong"
  );