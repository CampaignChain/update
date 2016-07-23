<?php
/*
 * Copyright 2016 CampaignChain, Inc. <info@campaignchain.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace CampaignChain\UpdateBundle\Service;

use Symfony\Component\Console\Style\SymfonyStyle;

interface DataUpdateInterface {

    /**
     * Return the version for the code update
     * The string has to be in date(YmdHis) format
     * ie: 20160621125928
     *
     * @return integer
     */
    public function getVersion();

    /**
     * A description about what the code update
     * It will be used in the console
     *
     * @return string
     */
    public function getDescription();

    /**
     * The code updates will
     *
     * @param SymfonyStyle $io
     *
     * @return boolean
     */
    public function execute(SymfonyStyle $io = null);
}