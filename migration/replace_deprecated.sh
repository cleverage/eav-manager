#!/usr/bin/env bash

find . -type f -exec sed -i 's/eavmanager_process.task.eav_reader/CleverAge\\EAVManager\\ProcessBundle\\Task\\EAVReaderTask/g' {} \;
find . -type f -exec sed -i 's/eavmanager_process.task.eav_criteria_reader/CleverAge\\EAVManager\\ProcessBundle\\Task\\EAVCriteriaReaderTask/g' {} \;
find . -type f -exec sed -i 's/eav_manager_process.transformer.unique_eav_finder/CleverAge\\EAVManager\\ProcessBundle\\Transformer\\UniqueEAVFinderTransformer/g' {} \;
find . -type f -exec sed -i 's/eav_manager_process.transformer.single_eav_finder/CleverAge\\EAVManager\\ProcessBundle\\Transformer\\SingleEAVFinderTransformer/g' {} \;
find . -type f -exec sed -i 's/eav_manager_process.transformer.resource_to_asset/CleverAge\\EAVManager\\ProcessBundle\\Transformer\\ResourceToAssetTransformer/g' {} \;
find . -type f -exec sed -i 's/eavmanager_user.user.manager/CleverAge\\EAVManager\\UserBundle\\Domain\\Manager\\UserManagerInterface/g' {} \;
find . -type f -exec sed -i 's/eavmanager_user.user.provider/CleverAge\\EAVManager\\UserBundle\\Security\\UserProvider/g' {} \;
