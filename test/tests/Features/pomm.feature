Feature: Pomm bridge for api platform
    Background:
        Given I add "accept" header equal to "application/json"

    Scenario: Index
        When I send a GET request to "/api"
        Then the JSON should be equal to:
        """
        {
            "resourceNameCollection": [
                "AppBundle\\Entity\\Config"
            ]
        }
        """

    Scenario: Retreive an empty collection
        When I send a GET request to "/api/configs"
        Then the JSON should be equal to:
        """
        []
        """

    Scenario Outline: Create a resource
        Given I add "Content-Type" header equal to "application/ld+json"
        When I send a POST request to "/api/configs" with body:
        """
        {
            "name": "<name>",
            "value": "<value>"
        }
        """

        Then the response status code should be 201
        And the JSON should be equal to:
        """
        {
            "name": "<name>",
            "value": "<value>",
            "status": 1
        }
        """

        Examples:
            | name  | value  |
            | test1 | value1 |
            | test2 | value2 |
            | test3 | value3 |
            | test4 | value4 |

    Scenario: Retreive a collection
        When I send a GET request to "/api/configs"
        Then the JSON should be equal to:
        """
        [
            {
                "name": "test1",
                "value": "value1",
                "status": 1
            },
            {
                "name": "test2",
                "value": "value2",
                "status": 1
            },
            {
                "name": "test3",
                "value": "value3",
                "status": 1
            },
            {
                "name": "test4",
                "value": "value4",
                "status": 1
            }
        ]
        """

    Scenario: Retreive resources order by desc
        When I send a GET request to "/api/configs?order[value]=desc"
        Then the JSON should be equal to:
        """
        [
            {
                "name": "test4",
                "value": "value4",
                "status": 1
            },
            {
                "name": "test3",
                "value": "value3",
                "status": 1
            },
            {
                "name": "test2",
                "value": "value2",
                "status": 1
            },
            {
                "name": "test1",
                "value": "value1",
                "status": 1
            }
        ]
        """

    Scenario: Modify a resource
        Given I add "Content-Type" header equal to "application/ld+json"
        When I send a PUT request to "/api/configs/test1" with body:
        """
        {
            "name": "test1",
            "value": "new_value",
            "status": 1
        }
        """
        Then the response status code should be 200
        And the JSON should be equal to:
        """
        {
            "name": "test1",
            "value": "new_value",
            "status": 1
        }
        """

    Scenario: Retreive a resource
        When I send a GET request to "/api/configs/test1"
        Then the JSON should be equal to:
        """
        {
            "name": "test1",
            "value": "new_value",
            "status": 1
        }
        """

    Scenario: Retreive resources with search filter
        When I send a GET request to "/api/configs?value=new_value"
        Then the JSON should be equal to:
        """
        [
            {
                "name": "test1",
                "value": "new_value",
                "status": 1
            }
        ]
        """

    Scenario Outline: Delete a resource
        When I send a DELETE request to "/api/configs/<id>"
        Then the response status code should be 204

        Examples:
            | id    |
            | test1 |
            | test2 |
            | test3 |
            | test4 |

    Scenario:
        When I send a GET request to "/api/configs"
        Then the JSON should be equal to:
        """
        []
        """
